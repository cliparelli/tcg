<?php

declare(strict_types=1);

/**
 * Varre LIB/ em busca de CSVs de cartas (Personagens, Itens, Energias),
 * detecta o tipo pelo nome do arquivo e devolve os registros já normalizados.
 */
final class CsvLibrary
{
    private const TYPE_PATTERNS = [
        'personagem' => '/PERSONAGENS/i',
        'item' => '/ITENS/i',
        'energia' => '/ENERGIAS/i',
    ];

    public function __construct(private readonly string $libDir)
    {
    }

    /**
     * @return array{collections: array<int, array{name: string, files: array<int, array{path: string, relPath: string, type: string, count: int}>}>}
     */
    public function scan(): array
    {
        $collections = [];

        if (!is_dir($this->libDir)) {
            return ['collections' => []];
        }

        $entries = scandir($this->libDir) ?: [];
        sort($entries);

        foreach ($entries as $entry) {
            if ($entry === '.' || $entry === '..') {
                continue;
            }

            $collectionPath = $this->libDir . DIRECTORY_SEPARATOR . $entry;
            if (!is_dir($collectionPath)) {
                continue;
            }

            $files = $this->scanCollection($collectionPath, $entry);
            if ($files !== []) {
                $collections[] = ['name' => $entry, 'files' => $files];
            }
        }

        return ['collections' => $collections];
    }

    /**
     * @return array<int, array{path: string, relPath: string, type: string, count: int}>
     */
    private function scanCollection(string $collectionPath, string $collectionName): array
    {
        $files = [];
        $csvPaths = glob($collectionPath . DIRECTORY_SEPARATOR . '*.csv') ?: [];
        sort($csvPaths);

        foreach ($csvPaths as $csvPath) {
            $type = $this->detectType(basename($csvPath));
            if ($type === null) {
                continue;
            }

            $count = count($this->readRows($csvPath, $type));
            $files[] = [
                'path' => $csvPath,
                'relPath' => $collectionName . '/' . basename($csvPath),
                'type' => $type,
                'count' => $count,
            ];
        }

        return $files;
    }

    private function detectType(string $filename): ?string
    {
        foreach (self::TYPE_PATTERNS as $type => $pattern) {
            if (preg_match($pattern, $filename) === 1) {
                return $type;
            }
        }

        return null;
    }

    /**
     * Lê um CSV específico pelo relPath (coleção/arquivo.csv) e devolve os registros.
     *
     * @return array<int, array<string, string>>
     */
    public function readByRelPath(string $relPath): array
    {
        $csvPath = realpath($this->libDir . DIRECTORY_SEPARATOR . $relPath);
        $libReal = realpath($this->libDir);

        if ($csvPath === false || $libReal === false || !str_starts_with($csvPath, $libReal)) {
            throw new InvalidArgumentException('Arquivo não encontrado dentro de LIB/.');
        }

        $type = $this->detectType(basename($csvPath));
        if ($type === null) {
            throw new InvalidArgumentException('CSV não reconhecido (esperado PERSONAGENS, ITENS ou ENERGIAS no nome).');
        }

        return $this->readRows($csvPath, $type);
    }

    /**
     * @return array<int, array<string, string>>
     */
    private function readRows(string $csvPath, string $type): array
    {
        $handle = fopen($csvPath, 'r');
        if ($handle === false) {
            return [];
        }

        $rows = [];

        try {
            $header = fgetcsv($handle, 0, ';');
            if ($header === false || $header === null) {
                return [];
            }

            $header = array_map(
                static fn (?string $col): string => str_replace("\n", ' ', trim((string) $col)),
                $header
            );

            $index = 0;
            while (($row = fgetcsv($handle, 0, ';')) !== false) {
                if ($row === [null] || $this->isBlankRow($row)) {
                    continue;
                }

                $record = [];
                foreach ($header as $i => $colName) {
                    if ($colName === '') {
                        continue;
                    }
                    $record[$colName] = trim((string) ($row[$i] ?? ''));
                }

                if (($record['Nome'] ?? '') === '') {
                    continue;
                }

                $record['_id'] = $type . '-' . $index;
                $record['_type'] = $type;
                $rows[] = $record;
                $index++;
            }
        } finally {
            fclose($handle);
        }

        return $rows;
    }

    /**
     * @param array<int, string|null> $row
     */
    private function isBlankRow(array $row): bool
    {
        foreach ($row as $value) {
            if (trim((string) $value) !== '') {
                return false;
            }
        }

        return true;
    }
}
