<?php

declare(strict_types=1);

/**
 * Mapeamento Tipo de personagem -> sigla de 3 letras (ver CLAUDE.md).
 */
final class CardTypes
{
    private const MAP = [
        'natureza' => 'NTZ',
        'vida' => 'LIF',
        'morte' => 'DTH',
        'divino' => 'DVN',
        'elemental' => 'ELM',
        'tecsci' => 'TEC',
        'magia' => 'MGC',
        'mental' => 'MTL',
        'físico' => 'FSC',
        'fisico' => 'FSC',
        'poder energético' => 'ENG',
        'poder energetico' => 'ENG',
        'fera' => 'FRL',
        'cósmico' => 'CSM',
        'cosmico' => 'CSM',
    ];

    public static function sigla(string $tipo): ?string
    {
        $key = mb_strtolower(trim($tipo));

        return self::MAP[$key] ?? null;
    }
}
