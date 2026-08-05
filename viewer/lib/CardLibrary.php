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
     * Atualiza stats.life/stats.defense de um personagem identificado por id,
     * regravando o JSON de origem (relPath) com os mesmos registros e ordem.
     *
     * @return array<string, mixed> registro atualizado
     */
    public function updateCharacterStats(string $relPath, string $cardId, ?int $life, ?int $defense): array
    {
        return $this->updateCharacterRecord($relPath, $cardId, function (\stdClass $record) use ($life, $defense): void {
            if (!isset($record->stats) || !($record->stats instanceof \stdClass)) {
                $record->stats = new \stdClass();
            }
            if ($life !== null) {
                $record->stats->life = $life;
            }
            if ($defense !== null) {
                $record->stats->defense = $defense;
            }
        });
    }

    /**
     * Atualiza um campo simples (name, description, kind, energyCost, keywords)
     * de actions[$actionIndex] de um personagem identificado por id.
     *
     * @return array<string, mixed> registro atualizado
     */
    public function updateCharacterAction(string $relPath, string $cardId, int $actionIndex, string $field, mixed $value): array
    {
        $allowedFields = ['name', 'description', 'kind', 'energyCost', 'keywords'];
        if (!in_array($field, $allowedFields, true)) {
            throw new InvalidArgumentException('Campo de action não editável: ' . $field);
        }

        return $this->updateCharacterRecord($relPath, $cardId, function (\stdClass $record) use ($actionIndex, $field, $value): void {
            if (!isset($record->actions) || !is_array($record->actions) || !isset($record->actions[$actionIndex])) {
                throw new InvalidArgumentException('Action não encontrada no índice informado.');
            }
            $action = $record->actions[$actionIndex];
            if (!($action instanceof \stdClass)) {
                throw new InvalidArgumentException('Estrutura de action inválida.');
            }

            if ($field === 'energyCost') {
                $action->energyCost = (int) $value;
            } elseif ($field === 'keywords') {
                $action->keywords = is_array($value) ? array_values($value) : [];
            } else {
                $action->$field = (string) $value;
            }
        });
    }

    /**
     * Localiza um registro por id em relPath, aplica $mutator sobre o
     * stdClass encontrado e regrava o arquivo preservando indentação/ordem.
     *
     * @param callable(\stdClass): void $mutator
     * @return array<string, mixed> registro atualizado
     */
    private function updateCharacterRecord(string $relPath, string $cardId, callable $mutator): array
    {
        $jsonPath = realpath($this->expansionsDir . DIRECTORY_SEPARATOR . str_replace('/', DIRECTORY_SEPARATOR, $relPath));
        $expansionsReal = realpath($this->expansionsDir);

        if ($jsonPath === false || $expansionsReal === false || !str_starts_with($jsonPath, $expansionsReal)) {
            throw new InvalidArgumentException('Arquivo não encontrado dentro de EXPANSIONS/.');
        }

        if ($this->detectType(basename($jsonPath)) !== 'personagem') {
            throw new InvalidArgumentException('Edição só é permitida em personagens.json.');
        }

        $contents = file_get_contents($jsonPath);
        if ($contents === false) {
            throw new InvalidArgumentException('Falha ao ler o arquivo.');
        }

        // assoc: false preserva a distinção {} (stdClass) vs [] (array) do
        // JSON original ao regravar; usar array associativo colapsaria ambos.
        $decoded = json_decode($contents);
        if (!is_array($decoded)) {
            throw new InvalidArgumentException('JSON inválido.');
        }

        $found = null;
        foreach ($decoded as $record) {
            if (!($record instanceof \stdClass)) {
                continue;
            }
            if ((string) ($record->id ?? '') === $cardId) {
                $mutator($record);
                $found = $record;
                break;
            }
        }

        if ($found === null) {
            throw new InvalidArgumentException('Carta não encontrada para o id informado.');
        }

        $written = file_put_contents(
            $jsonPath,
            self::encodeWithTwoSpaceIndent($decoded) . "\n"
        );
        if ($written === false) {
            throw new InvalidArgumentException('Falha ao gravar o arquivo.');
        }

        $collection = basename(dirname($jsonPath, 2));
        $result = json_decode(json_encode($found), true);
        $result['_id'] = (string) ($result['id'] ?? $cardId);
        $result['_type'] = 'personagem';
        $result['_collection'] = $collection;

        return $result;
    }

    /**
     * json_encode(..., JSON_PRETTY_PRINT) sempre indenta com 4 espaços por
     * nível; os schemas em EXPANSIONS/ usam 2. Reindenta linha a linha
     * (olhando só o whitespace inicial) para não reformatar o arquivo
     * inteiro a cada gravação.
     */
    private static function encodeWithTwoSpaceIndent(mixed $data): string
    {
        $json = json_encode($data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT);
        if ($json === false) {
            throw new InvalidArgumentException('Falha ao codificar JSON.');
        }

        $lines = explode("\n", $json);
        foreach ($lines as $i => $line) {
            if (preg_match('/^( +)/', $line, $m)) {
                $level = (int) (strlen($m[1]) / 4);
                $lines[$i] = str_repeat('  ', $level) . substr($line, strlen($m[1]));
            }
        }

        return implode("\n", $lines);
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
