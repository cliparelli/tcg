<?php

declare(strict_types=1);

/**
 * Serve os templates de moldura (CARD-MODEL / LAND-MODEL) de CARDS/ASSETS/STRUCTURES/V6,
 * ou (com ?art=1) a arte de uma carta em LIB/{Coleção}/ARTE/{arquivo}, apontada
 * pela coluna "Arte" do CSV.
 */

if (($_GET['art'] ?? '') !== '') {
    serveArt();
    exit;
}

$structuresDir = dirname(__DIR__) . '/CARDS/ASSETS/STRUCTURES/V6';

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

function serveArt(): void
{
    $collection = (string) ($_GET['collection'] ?? '');
    $file = (string) ($_GET['file'] ?? '');

    if ($collection === '' || $file === '') {
        http_response_code(400);
        exit;
    }

    $libDir = dirname(__DIR__) . '/LIB';
    // $artDir = realpath($libDir . '/' . $collection . '/ARTE');
    $artDir = realpath($libDir . '/' . $collection . '');
    $libReal = realpath($libDir);

    if ($artDir === false || $libReal === false || !str_starts_with($artDir, $libReal)) {
        http_response_code(404);
        exit;
    }

    $path = realpath($artDir . '/' . $file);
    if ($path === false || !str_starts_with($path, $artDir) || !is_file($path)) {
        http_response_code(404);
        exit;
    }

    $mime = match (strtolower(pathinfo($path, PATHINFO_EXTENSION))) {
        'png' => 'image/png',
        'jpg', 'jpeg' => 'image/jpeg',
        'webp' => 'image/webp',
        default => 'application/octet-stream',
    };

    header('Content-Type: ' . $mime);
    header('Cache-Control: no-cache');
    readfile($path);
}
