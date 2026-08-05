<?php

declare(strict_types=1);

/**
 * Varre EXPANSIONS/{Coleção}/schemas/{personagens,itens,energias}.json
 * (schema v1 de schemas/schema-cartas.md) e devolve os registros já
 * normalizados, com _id/_type/_collection anexados.
 */
final class CardLibrary
{
    private const TYPE_FILES = [
        'personagem' => 'personagens.json',
        'item' => 'itens.json',
        'energia' => 'energias.json',
    ];

    public function __construct(private readonly string $expansionsDir)
    {
    }

    /**
     * @return array{collections: array<int, array{name: string, files: array<int, array{path: string, relPath: string, type: string, count: int}>}>}
     */
    public function scan(): array
    {
        $collections = [];

        if (!is_dir($this->expansionsDir)) {
            return ['collections' => []];
        }

        $entries = scandir($this->expansionsDir) ?: [];
        sort($entries);

        foreach ($entries as $entry) {
            if ($entry === '.' || $entry === '..') {
                continue;
            }

            $schemasDir = $this->expansionsDir . DIRECTORY_SEPARATOR . $entry . DIRECTORY_SEPARATOR . 'schemas';
            if (!is_dir($schemasDir)) {
                continue;
            }

            $files = $this->scanCollection($schemasDir, $entry);
            if ($files !== []) {
                $collections[] = ['name' => $entry, 'files' => $files];
            }
        }

        return ['collections' => $collections];
    }

    /**
     * @return array<int, array{path: string, relPath: string, type: string, count: int}>
     */
    private function scanCollection(string $schemasDir, string $collectionName): array
    {
        $files = [];

        foreach (self::TYPE_FILES as $type => $filename) {
            $jsonPath = $schemasDir . DIRECTORY_SEPARATOR . $filename;
            if (!is_file($jsonPath)) {
                continue;
            }

            $records = $this->readJson($jsonPath);
            if ($records === []) {
                continue;
            }

            $files[] = [
                'path' => $jsonPath,
                'relPath' => $collectionName . '/schemas/' . $filename,
                'type' => $type,
                'count' => count($records),
            ];
        }

        return $files;
    }

    private function detectType(string $filename): ?string
    {
        $type = array_search($filename, self::TYPE_FILES, true);

        return $type === false ? null : $type;
    }

    /**
     * Lê um JSON específico pelo relPath (coleção/arquivo.json) e devolve os registros.
     *
     * @return array<int, array<string, mixed>>
     */
    public function readByRelPath(string $relPath): array
    {
        $jsonPath = realpath($this->expansionsDir . DIRECTORY_SEPARATOR . str_replace('/', DIRECTORY_SEPARATOR, $relPath));
        $expansionsReal = realpath($this->expansionsDir);

        if ($jsonPath === false || $expansionsReal === false || !str_starts_with($jsonPath, $expansionsReal)) {
            throw new InvalidArgumentException('Arquivo não encontrado dentro de EXPANSIONS/.');
        }

        $type = $this->detectType(basename($jsonPath));
        if ($type === null) {
            throw new InvalidArgumentException('JSON não reconhecido (esperado personagens.json, itens.json ou energias.json).');
        }

        $collection = basename(dirname($jsonPath, 2));

        return $this->readJson($jsonPath, $collection, $type);
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    private function readJson(string $jsonPath, ?string $collection = null, ?string $type = null): array
    {
        $contents = file_get_contents($jsonPath);
        if ($contents === false) {
            return [];
        }

        $decoded = json_decode($contents, true);
        if (!is_array($decoded)) {
            return [];
        }

        $type ??= $this->detectType(basename($jsonPath));
        $collection ??= basename(dirname($jsonPath, 2));

        $records = [];
        foreach (array_values($decoded) as $index => $record) {
            if (!is_array($record)) {
                continue;
            }

            $record['_id'] = (string) ($record['id'] ?? ($type . '-' . $index));
            $record['_type'] = $type;
            $record['_collection'] = $collection;
            $records[] = $record;
        }

        return $records;
    }
}
