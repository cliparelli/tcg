<?php

declare(strict_types=1);

/**
 * Faz o parse de decklists em Markdown (public/decks/*.md), no formato descrito
 * no CLAUDE.md: título, parágrafos de estratégia, seções "## Personagens (N)" etc,
 * subseções por categoria e linhas "- NxNome (tag) - Categoria *".
 */
final class DeckParser
{
    private const RARITY_PATTERN = 'Básica|Basica|Comum|Rara|Épico|Epico|Lendária|Lendaria|Prismática|Prismatica|Avançada|Avancada';

    /**
     * @return array{title: string, strategy: string, sections: array<int, array{name: string, groups: array<int, array{name: string, entries: array<int, array{qty: int, name: string, tags: array<int, string>, rarity: string, category: string, combo: bool}>}>}>}
     */
    public static function parse(string $markdown): array
    {
        $lines = preg_split('/\r\n|\r|\n/', $markdown) ?: [];

        $title = '';
        $strategyLines = [];
        $sections = [];

        $sectionIdx = -1;
        $groupIdx = -1;
        $inStrategy = false;

        foreach ($lines as $rawLine) {
            $trimmed = trim(rtrim($rawLine));

            if ($trimmed === '') {
                continue;
            }

            if (str_starts_with($trimmed, '# ')) {
                $title = trim(substr($trimmed, 2));
                $inStrategy = true;
                continue;
            }

            if (str_starts_with($trimmed, '## ')) {
                $inStrategy = false;
                $heading = trim(substr($trimmed, 3));
                $name = preg_replace('/\s*\(\d+\)\s*$/', '', $heading) ?? $heading;
                $sections[] = ['name' => trim($name), 'groups' => []];
                $sectionIdx = count($sections) - 1;
                $groupIdx = -1;
                continue;
            }

            // Subseção de categoria: "- Líderes", "- Permanente", etc. (sem "x" de quantidade e sem indentação de item)
            if (preg_match('/^-\s+([A-Za-zÀ-ÿ][^\n]*)$/u', $trimmed, $m) === 1 && !preg_match('/^\d+x/i', $m[1])) {
                $inStrategy = false;
                if ($sectionIdx >= 0) {
                    $sections[$sectionIdx]['groups'][] = ['name' => trim($m[1]), 'entries' => []];
                    $groupIdx = count($sections[$sectionIdx]['groups']) - 1;
                }
                continue;
            }

            // Linha de carta: "- NxNome (tag) (tag2) - Categoria *"
            if (preg_match('/^-\s*(\d+)\s*x\s*(.+)$/iu', $trimmed, $m) === 1) {
                $inStrategy = false;
                $qty = (int) $m[1];
                $rest = trim($m[2]);
                $combo = false;
                if (preg_match('/\*\s*$/', $rest) === 1) {
                    $combo = true;
                    $rest = trim(preg_replace('/\*\s*$/', '', $rest) ?? $rest);
                }

                $tags = [];
                if (preg_match_all('/\(([^()]*)\)/', $rest, $tm) > 0) {
                    foreach ($tm[1] as $tagValue) {
                        $tags[] = trim($tagValue);
                    }
                    $rest = trim(preg_replace('/\s*\([^()]*\)/', '', $rest) ?? $rest);
                }

                $category = '';
                if (preg_match('/^(.*?)\s-\s([^-]+)$/u', $rest, $cm) === 1) {
                    $rest = trim($cm[1]);
                    $category = trim($cm[2]);
                }

                $rarity = '';
                if (preg_match('/^(.*?)\s-\s(' . self::RARITY_PATTERN . ')$/iu', $rest, $rm) === 1) {
                    $rest = trim($rm[1]);
                    $rarity = trim($rm[2]);
                }

                $entry = [
                    'qty' => $qty,
                    'name' => $rest,
                    'tags' => $tags,
                    'rarity' => $rarity,
                    'category' => $category,
                    'combo' => $combo,
                ];

                if ($sectionIdx >= 0) {
                    if ($groupIdx < 0) {
                        // Seção sem subgrupo explícito: cria um grupo default
                        $sections[$sectionIdx]['groups'][] = ['name' => '', 'entries' => []];
                        $groupIdx = count($sections[$sectionIdx]['groups']) - 1;
                    }
                    $sections[$sectionIdx]['groups'][$groupIdx]['entries'][] = $entry;
                }
                continue;
            }

            if ($inStrategy) {
                $strategyLines[] = $trimmed;
            }
        }

        return [
            'title' => $title,
            'strategy' => trim(implode("\n\n", $strategyLines)),
            'sections' => $sections,
        ];
    }
}
