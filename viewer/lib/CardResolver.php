<?php

declare(strict_types=1);

require_once __DIR__ . '/CsvLibrary.php';

/**
 * Casa entradas de decklist (nome + tags entre parênteses, ex. "Milo de Escorpião
 * Divino (SS)" ou "Yemira das Mil Dunas (v1) (Fratura do Multiverso)") com os
 * registros reais lidos via CsvLibrary, tentando por nome + coleção/universo/versão.
 */
final class CardResolver
{
    private const UNIVERSE_ALIASES = [
        'ss' => 'saint seiya',
        'basic' => 'basic',
        'minecraft' => 'minecraft',
        'dc' => 'dc',
        'death note' => 'death note',
    ];

    private const TYPE_FILE_PREFIX = [
        'personagem' => 'personagem',
        'item' => 'item',
        'energia' => 'energia',
    ];

    /** @var array<int, array{collection: string, type: string, records: array<int, array<string, string>>}>|null */
    private ?array $index = null;

    public function __construct(
        private readonly CsvLibrary $library,
        private readonly ?string $expansionsDir = null,
    ) {
    }

    /**
     * @param array<int, string> $tags
     * @return array{record: array<string, string>, type: string, collection: string}|null
     */
    public function resolve(string $name, array $tags): ?array
    {
        $this->buildIndex();

        $needle = self::normalize($name);
        $candidates = [];

        foreach ($this->index ?? [] as $bucket) {
            foreach ($bucket['records'] as $record) {
                $recordName = self::normalize($record['Nome'] ?? '');
                if ($recordName === '') {
                    continue;
                }
                if ($recordName === $needle) {
                    $candidates[] = ['record' => $record, 'type' => $bucket['type'], 'collection' => $bucket['collection']];
                }
            }
        }

        if ($candidates === []) {
            return null;
        }

        if (count($candidates) === 1 || $tags === []) {
            return $candidates[0];
        }

        $normalizedTags = array_map(self::normalize(...), $tags);

        foreach ($candidates as $candidate) {
            if ($this->matchesTags($candidate, $normalizedTags)) {
                return $candidate;
            }
        }

        return $candidates[0];
    }

    /**
     * @param array{record: array<string, string>, type: string, collection: string} $candidate
     * @param array<int, string> $normalizedTags
     */
    private function matchesTags(array $candidate, array $normalizedTags): bool
    {
        $record = $candidate['record'];
        $collection = self::normalize($candidate['collection']);
        $universo = self::normalize($record['Universo'] ?? '');
        $versao = self::normalize($record['Versão'] ?? '');

        foreach ($normalizedTags as $tag) {
            $aliased = self::UNIVERSE_ALIASES[$tag] ?? $tag;

            if ($collection === $tag || $collection === $aliased) {
                return true;
            }
            if ($universo !== '' && ($universo === $tag || $universo === $aliased)) {
                return true;
            }
            if ($versao !== '' && ($versao === $tag || $versao === ltrim($tag, 'v'))) {
                return true;
            }
        }

        return false;
    }

    private function buildIndex(): void
    {
        if ($this->index !== null) {
            return;
        }

        $this->index = [];
        $scan = $this->library->scan();

        foreach ($scan['collections'] as $collection) {
            foreach ($collection['files'] as $file) {
                $records = $this->library->readByRelPath($file['relPath']);
                $this->index[] = [
                    'collection' => $collection['name'],
                    'type' => $file['type'],
                    'records' => $records,
                ];
            }
        }
    }

    /**
     * Localiza, em EXPANSIONS/{coleção}/, o PNG já renderizado da carta
     * ("{tipo}-{Nome}.png" ou, se ainda não tiver arte, "{tipo}-{Nome}-sem-imagem.png").
     */
    public function resolveExpansionImage(string $collection, string $type, string $name): ?string
    {
        if ($this->expansionsDir === null || $collection === '') {
            return null;
        }

        $prefix = self::TYPE_FILE_PREFIX[$type] ?? null;
        if ($prefix === null) {
            return null;
        }

        $collectionDir = realpath($this->expansionsDir . '/' . $collection);
        $expansionsReal = realpath($this->expansionsDir);
        if ($collectionDir === false || $expansionsReal === false || !str_starts_with($collectionDir, $expansionsReal)) {
            return null;
        }

        $files = glob($collectionDir . '/' . $prefix . '-*.png') ?: [];
        $needle = self::normalizeSlug($name);

        $withImage = null;
        $withoutImage = null;

        foreach ($files as $path) {
            $basename = basename($path, '.png');
            $rest = substr($basename, strlen($prefix) + 1);
            $hasPlaceholder = str_ends_with($rest, '-sem-imagem');
            if ($hasPlaceholder) {
                $rest = substr($rest, 0, -strlen('-sem-imagem'));
            }

            $fileName = self::normalizeSlug($rest);
            if ($fileName !== $needle) {
                continue;
            }

            if ($hasPlaceholder) {
                $withoutImage ??= $path;
            } else {
                $withImage ??= $path;
            }
        }

        $match = $withImage ?? $withoutImage;
        if ($match === null) {
            return null;
        }

        return $collection . '/' . basename($match);
    }

    /**
     * Normaliza um nome/slug para comparação: remove acentos, caixa e qualquer
     * pontuação/espaçamento, já que os nomes de arquivo em EXPANSIONS/ descartam
     * vírgulas e usam "-" tanto para espaço quanto para hífens do nome original.
     */
    private static function normalizeSlug(string $value): string
    {
        $value = self::normalize(str_replace('-', ' ', $value));

        return preg_replace('/[^a-z0-9]/', '', $value) ?? $value;
    }

    private static function normalize(string $value): string
    {
        $value = mb_strtolower(trim($value));
        $value = str_replace(['á', 'à', 'ã', 'â', 'ä'], 'a', $value);
        $value = str_replace(['é', 'è', 'ê', 'ë'], 'e', $value);
        $value = str_replace(['í', 'ì', 'î', 'ï'], 'i', $value);
        $value = str_replace(['ó', 'ò', 'õ', 'ô', 'ö'], 'o', $value);
        $value = str_replace(['ú', 'ù', 'û', 'ü'], 'u', $value);
        $value = str_replace('ç', 'c', $value);
        $value = preg_replace('/\s+/', ' ', $value) ?? $value;

        return trim($value);
    }
}
