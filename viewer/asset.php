<?php

declare(strict_types=1);

/**
 * Serve os templates de moldura (CARD-MODEL / LAND-MODEL) de CARDS/ASSETS/STRUCTURES/V6,
 * ou (com ?expansion=1) a arte de uma carta em EXPANSIONS/{Coleção}/{arquivo}
 * (relPath relativo a EXPANSIONS/, ex. "Fratura do Multiverso/card-art/{assetRef}").
 */

if (($_GET['expansion'] ?? '') !== '') {
    serveExpansionImage();
    exit;
}

$structuresDir = __DIR__;

$allowed = [
    'card' => 'CARD-MODEL.png',
    'land' => 'LAND-MODEL.png',
];

$key = (string) ($_GET['model'] ?? 'card');
$filename = $allowed[$key] ?? $allowed['card'];

$path = $structuresDir . '/' . $filename;
if (!is_file($path)) {
    $path = $structuresDir . '/' . $allowed['card'];
}

if (!is_file($path)) {
    http_response_code(404);
    exit;
}

header('Content-Type: image/png');
header('Cache-Control: no-cache');
readfile($path);

function serveExpansionImage(): void
{
    $file = (string) ($_GET['file'] ?? '');

    if ($file === '') {
        http_response_code(400);
        exit;
    }

    $expansionsDir = dirname(__DIR__) . '/EXPANSIONS';
    $expansionsReal = realpath($expansionsDir);

    $path = realpath($expansionsDir . '/' . $file);
    if ($path === false || $expansionsReal === false || !str_starts_with($path, $expansionsReal) || !is_file($path)) {
        http_response_code(404);
        exit;
    }

    header('Content-Type: image/png');
    header('Cache-Control: no-cache');
    readfile($path);
}
