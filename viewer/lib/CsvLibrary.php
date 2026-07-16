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
     * Atualiza os campos de uma linha existente (identificada por _id) e regrava o CSV.
     *
     * @param array<string, string> $fields
     */
    public function updateRow(string $relPath, string $id, array $fields): void
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

        $readHandle = fopen($csvPath, 'r');
        if ($readHandle === false) {
            throw new InvalidArgumentException('Não foi possível abrir o arquivo para leitura.');
        }

        $header = [];
        $lines = [];
        $found = false;

        try {
            $header = fgetcsv($readHandle, 0, ';');
            if ($header === false || $header === null) {
                throw new InvalidArgumentException('CSV sem cabeçalho.');
            }
            $header = array_map(
                static fn (?string $col): string => str_replace("\n", ' ', trim((string) $col)),
                $header
            );

            $index = 0;
            while (($row = fgetcsv($readHandle, 0, ';')) !== false) {
                if ($row === [null] || $this->isBlankRow($row)) {
                    $lines[] = ['raw' => $row, 'blank' => true];
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
                    $lines[] = ['raw' => $row, 'blank' => true];
                    continue;
                }

                $rowId = $type . '-' . $index;
                if ($rowId === $id) {
                    foreach ($fields as $key => $value) {
                        if ($key === '' || str_starts_with($key, '_') || !in_array($key, $header, true)) {
                            continue;
                        }
                        $record[$key] = $value;
                    }
                    $found = true;
                }

                $lines[] = ['raw' => $row, 'blank' => false, 'record' => $record];
                $index++;
            }
        } finally {
            fclose($readHandle);
        }

        if (!$found) {
            throw new InvalidArgumentException('Carta não encontrada para atualização.');
        }

        $writeHandle = fopen($csvPath, 'w');
        if ($writeHandle === false) {
            throw new InvalidArgumentException('Não foi possível abrir o arquivo para escrita.');
        }

        try {
            fwrite($writeHandle, $this->csvLine($header) . "\n");
            foreach ($lines as $line) {
                if ($line['blank']) {
                    fwrite($writeHandle, $this->csvLine($line['raw']) . "\n");
                    continue;
                }
                $out = [];
                foreach ($header as $colName) {
                    $out[] = $colName === '' ? '' : ($line['record'][$colName] ?? '');
                }
                fwrite($writeHandle, $this->csvLine($out) . "\n");
            }
        } finally {
            fclose($writeHandle);
        }
    }

    /**
     * Monta uma linha CSV com ";" como delimitador, citando campos apenas quando
     * necessário (contêm ";", aspas ou quebra de linha), no mesmo estilo dos CSVs existentes.
     *
     * @param array<int, string|null> $fields
     */
    private function csvLine(array $fields): string
    {
        $parts = [];
        foreach ($fields as $field) {
            $value = (string) ($field ?? '');
            if (preg_match('/[";,\n\r]/', $value) === 1) {
                $parts[] = '"' . str_replace('"', '""', $value) . '"';
            } else {
                $parts[] = $value;
            }
        }

        return implode(';', $parts);
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
