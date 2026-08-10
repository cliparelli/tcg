<?php

declare(strict_types=1);

require_once __DIR__ . '/CardLibrary.php';

/**
 * Varre EXPANSIONS/{Coleção}/schemas/decks/*.json (schema v1 de
 * schemas/schema-deck.md) e resolve entries[]/sideboard[] contra os
 * schemas de carta (personagens/itens/energias) via cardId.
 */
final class DeckLibrary
{
    /** @var array<string, array{record: array<string, mixed>, type: string, collection: string}>|null */
    private ?array $cardIndex = null;

    public function __construct(
        private readonly string $expansionsDir,
        private readonly CardLibrary $cardLibrary,
    ) {
    }

    /**
     * @return array<int, array{file: string, title: string, style: string, types: array<int, string>}>
     */
    public function listDecks(): array
    {
        if (!is_dir($this->expansionsDir)) {
            return [];
        }

        $decks = [];
        $collectionDirs = glob($this->expansionsDir . '/*', GLOB_ONLYDIR) ?: [];
        sort($collectionDirs);

        foreach ($collectionDirs as $collectionDir) {
            $decksDir = $collectionDir . '/schemas/decks';
            if (!is_dir($decksDir)) {
                continue;
            }

            $collection = basename($collectionDir);
            $files = glob($decksDir . '/*.json') ?: [];
            sort($files);

            foreach ($files as $path) {
                $decoded = json_decode(file_get_contents($path) ?: '', true);
                if (!is_array($decoded)) {
                    continue;
                }

                $relFile = $collection . '/schemas/decks/' . basename($path);
                $entries = $this->resolveEntries($decoded['entries'] ?? []);

                $decks[] = [
                    'file' => $relFile,
                    'title' => (string) ($decoded['name'] ?? basename($path, '.json')),
                    'style' => self::deckStyle(basename($path, '.json')),
                    'types' => self::dominantTypes($entries),
                ];
            }
        }

        return $decks;
    }

    /**
     * @return array{
     *   title: string,
     *   description: string|null,
     *   strategy: array<string, mixed>,
     *   entries: array<int, array<string, mixed>>,
     *   sideboard: array<int, array<string, mixed>>
     * }
     */
    public function readDeck(string $relPath): array
    {
        $jsonPath = realpath($this->expansionsDir . DIRECTORY_SEPARATOR . str_replace('/', DIRECTORY_SEPARATOR, $relPath));
        $expansionsReal = realpath($this->expansionsDir);

        if (
            $jsonPath === false
            || $expansionsReal === false
            || !str_starts_with($jsonPath, $expansionsReal)
            || !str_contains($relPath, '/schemas/decks/')
        ) {
            throw new InvalidArgumentException('Deck não encontrado.');
        }

        $decoded = json_decode(file_get_contents($jsonPath) ?: '', true);
        if (!is_array($decoded)) {
            throw new InvalidArgumentException('JSON de deck inválido.');
        }

        return [
            'title' => (string) ($decoded['name'] ?? basename($jsonPath, '.json')),
            'description' => $decoded['description'] ?? null,
            'strategy' => $decoded['strategy'] ?? [],
            'entries' => $this->resolveEntries($decoded['entries'] ?? []),
            'sideboard' => $this->resolveEntries($decoded['sideboard'] ?? []),
        ];
    }

    /**
     * Cria um novo deck vazio (template de schema-deck.md) em
     * EXPANSIONS/{collection}/schemas/decks/{slug}.json. Falha se o slug já
     * existir, para não sobrescrever um deck existente por engano.
     *
     * @param array{id?: string, slug: string, name: string, description?: string|null, format?: string} $meta
     */
    public function createDeck(string $collection, array $meta): string
    {
        $slug = trim((string) ($meta['slug'] ?? ''));
        if ($collection === '') {
            throw new InvalidArgumentException('Coleção é obrigatória.');
        }
        if ($slug === '') {
            throw new InvalidArgumentException('Slug é obrigatório.');
        }
        if (!preg_match('/^[a-z0-9\-]+$/', $slug)) {
            throw new InvalidArgumentException(
                'Slug inválido: "' . $slug . '". Use apenas letras minúsculas, números e hífen (ex.: mono-tema-mundo).'
            );
        }

        $expansionsReal = realpath($this->expansionsDir);
        if ($expansionsReal === false || !is_dir($this->expansionsDir . '/' . $collection . '/schemas')) {
            throw new InvalidArgumentException('Coleção não encontrada: "' . $collection . '".');
        }

        $decksDir = $this->expansionsDir . '/' . $collection . '/schemas/decks';

        if (!is_dir($decksDir) && !mkdir($decksDir, 0777, true) && !is_dir($decksDir)) {
            throw new InvalidArgumentException('Falha ao criar pasta de decks da coleção.');
        }

        $jsonPath = $decksDir . '/' . $slug . '.json';
        if (file_exists($jsonPath)) {
            throw new InvalidArgumentException('Já existe um deck com o slug "' . $slug . '" nesta coleção.');
        }

        $deck = [
            'id' => (string) ($meta['id'] ?? $slug),
            'slug' => $slug,
            'name' => (string) ($meta['name'] ?? $slug),
            'description' => $meta['description'] ?? null,
            'format' => (string) ($meta['format'] ?? 'standard'),
            'strategy' => [
                'archetype' => null,
                'winCondition' => null,
                'keyMechanics' => [],
                'coreCombo' => null,
                'tempo' => null,
            ],
            'entries' => [],
            'sideboard' => [],
        ];

        $written = file_put_contents($jsonPath, self::encodeWithTwoSpaceIndent($deck) . "\n");
        if ($written === false) {
            throw new InvalidArgumentException('Falha ao gravar o arquivo do deck.');
        }

        $this->cardIndex = null;

        return $collection . '/schemas/decks/' . $slug . '.json';
    }

    /**
     * Regrava o deck completo em $relPath (deve já existir, dentro de
     * .../schemas/decks/). Não valida legalidade de deck (regras de
     * montagem) — isso é responsabilidade do client; um rascunho incompleto
     * é um estado válido conforme schema-deck.md.
     *
     * @param array<string, mixed> $deck
     */
    public function saveDeck(string $relPath, array $deck): void
    {
        $decksDir = realpath($this->expansionsDir . '/' . dirname($relPath));
        $expansionsReal = realpath($this->expansionsDir);
        $jsonPath = $this->expansionsDir . DIRECTORY_SEPARATOR . str_replace('/', DIRECTORY_SEPARATOR, $relPath);
        $existing = realpath($jsonPath);

        if (
            $expansionsReal === false
            || $decksDir === false
            || !str_starts_with($decksDir, $expansionsReal)
            || !str_contains($relPath, '/schemas/decks/')
            || $existing === false
            || !str_starts_with($existing, $expansionsReal)
        ) {
            throw new InvalidArgumentException('Deck não encontrado.');
        }

        $payload = [
            'id' => (string) ($deck['id'] ?? basename($relPath, '.json')),
            'slug' => (string) ($deck['slug'] ?? basename($relPath, '.json')),
            'name' => (string) ($deck['name'] ?? ''),
            'description' => $deck['description'] ?? null,
            'format' => (string) ($deck['format'] ?? 'standard'),
            'strategy' => is_array($deck['strategy'] ?? null) ? $deck['strategy'] : [
                'archetype' => null,
                'winCondition' => null,
                'keyMechanics' => [],
                'coreCombo' => null,
                'tempo' => null,
            ],
            'entries' => self::sanitizeEntries($deck['entries'] ?? [], true),
            'sideboard' => self::sanitizeEntries($deck['sideboard'] ?? [], false),
        ];

        $written = file_put_contents($existing, self::encodeWithTwoSpaceIndent($payload) . "\n");
        if ($written === false) {
            throw new InvalidArgumentException('Falha ao gravar o arquivo do deck.');
        }

        $this->cardIndex = null;
    }

    /**
     * @param mixed $entries
     * @return array<int, array<string, mixed>>
     */
    private static function sanitizeEntries(mixed $entries, bool $withPlayMeta): array
    {
        if (!is_array($entries)) {
            return [];
        }

        $sanitized = [];
        foreach ($entries as $entry) {
            if (!is_array($entry)) {
                continue;
            }
            $cardId = trim((string) ($entry['cardId'] ?? ''));
            if ($cardId === '') {
                continue;
            }

            $item = [
                'cardId' => $cardId,
                'quantity' => max(0, (int) ($entry['quantity'] ?? 0)),
            ];

            if ($withPlayMeta) {
                $item['suggestedRole'] = $entry['suggestedRole'] ?? null;
                $item['suggestedPlay'] = $entry['suggestedPlay'] ?? null;
                $item['designNotes'] = $entry['designNotes'] ?? null;
            }

            $sanitized[] = $item;
        }

        return $sanitized;
    }

    /**
     * json_encode(..., JSON_PRETTY_PRINT) sempre indenta com 4 espaços por
     * nível; os schemas em EXPANSIONS/ usam 2. Reindenta linha a linha
     * (mesma técnica de CardLibrary::encodeWithTwoSpaceIndent).
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
     * @param array<int, array<string, mixed>> $rawEntries
     * @return array<int, array<string, mixed>>
     */
    private function resolveEntries(array $rawEntries): array
    {
        $this->buildCardIndex();

        $resolved = [];
        foreach ($rawEntries as $entry) {
            if (!is_array($entry)) {
                continue;
            }

            $cardId = (string) ($entry['cardId'] ?? '');
            $match = $this->cardIndex[$cardId] ?? null;

            $resolved[] = [
                'cardId' => $cardId,
                'quantity' => (int) ($entry['quantity'] ?? 0),
                'suggestedRole' => $entry['suggestedRole'] ?? null,
                'suggestedPlay' => $entry['suggestedPlay'] ?? null,
                'designNotes' => $entry['designNotes'] ?? null,
                'card' => $match['record'] ?? null,
                'cardType' => $match['type'] ?? null,
                'collection' => $match['collection'] ?? null,
                'expansionImage' => $match !== null
                    ? $this->resolveExpansionImage($match['collection'], $match['type'], (string) ($match['record']['name'] ?? ''))
                    : null,
            ];
        }

        return $resolved;
    }

    private function buildCardIndex(): void
    {
        if ($this->cardIndex !== null) {
            return;
        }

        $this->cardIndex = [];
        $scan = $this->cardLibrary->scan();

        foreach ($scan['collections'] as $collection) {
            foreach ($collection['files'] as $file) {
                foreach ($this->cardLibrary->readByRelPath($file['relPath']) as $record) {
                    $id = (string) ($record['id'] ?? '');
                    if ($id === '') {
                        continue;
                    }
                    $this->cardIndex[$id] = [
                        'record' => $record,
                        'type' => $file['type'],
                        'collection' => $collection['name'],
                    ];
                }
            }
        }
    }

    /**
     * Localiza a arte da carta em EXPANSIONS/{coleção}/imgs/, cujo nome de
     * arquivo segue o padrão "{tipo}-{Nome-Sem-Acentos-Com-Hifens}.png"
     * (ex. "personagem-Yuna-Sete-Vitorias.png"). Quando existe também uma
     * versão "-sem-imagem.png" (placeholder de arte ainda não gerada), a
     * versão sem sufixo tem prioridade.
     */
    private function resolveExpansionImage(string $collection, string $cardType, string $name): ?string
    {
        if ($collection === '' || $cardType === '' || $name === '') {
            return null;
        }

        $expansionsReal = realpath($this->expansionsDir);
        $imgsDir = $this->expansionsDir . '/' . $collection . '/imgs';
        $base = $cardType . '-' . self::slugifyImageName($name);

        foreach (['.png', '-sem-imagem.png'] as $suffix) {
            $path = realpath($imgsDir . '/' . $base . $suffix);
            if ($path !== false && $expansionsReal !== false && str_starts_with($path, $expansionsReal)) {
                return $collection . '/imgs/' . basename($path);
            }
        }

        return null;
    }

    /**
     * Reproduz a convenção de nomeação de EXPANSIONS/*\/imgs/: remove
     * acentos, remove vírgulas, e troca qualquer sequência de caracteres
     * não alfanuméricos (espaço, apóstrofo, etc.) por um único hífen,
     * preservando a capitalização original do nome da carta.
     */
    private static function slugifyImageName(string $name): string
    {
        $transliterated = @iconv('UTF-8', 'ASCII//TRANSLIT//IGNORE', $name);
        if ($transliterated === false) {
            $transliterated = $name;
        }

        $noCommas = str_replace(',', '', $transliterated);
        $hyphenated = preg_replace('/[^A-Za-z0-9\-]+/', '-', $noCommas) ?? $noCommas;
        $collapsed = preg_replace('/-+/', '-', $hyphenated) ?? $hyphenated;

        return trim($collapsed, '-');
    }

    /**
     * Estilo (MONO/DUAL/TRIPLE/RAINBOW) a partir do prefixo do slug do
     * arquivo — mais confiável que o campo "name", que em alguns decks
     * ainda carrega o título cru do .md de origem (ex. "# Bi ...").
     */
    private static function deckStyle(string $slug): string
    {
        if (preg_match('/^([a-z]+)-/i', $slug, $m) === 1) {
            return strtoupper($m[1]);
        }

        return '';
    }

    /**
     * @param array<int, array<string, mixed>> $entries
     * @return array<int, string>
     */
    private static function dominantTypes(array $entries): array
    {
        $counts = [];

        foreach ($entries as $entry) {
            if (($entry['cardType'] ?? null) !== 'personagem') {
                continue;
            }
            $tipo = trim((string) ($entry['card']['worldType'] ?? ''));
            if ($tipo === '') {
                continue;
            }
            $counts[$tipo] = ($counts[$tipo] ?? 0) + (int) ($entry['quantity'] ?? 1);
        }

        arsort($counts);

        return array_keys($counts);
    }
}
