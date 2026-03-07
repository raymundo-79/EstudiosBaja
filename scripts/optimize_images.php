<?php
/**
 * Optimiza imágenes generando AVIF cuando la extensión GD lo permite.
 *
 * - Genera .avif en: img/optimized-avif/ (si imageavif está disponible)
 *
 * Uso:
 *   php scripts/optimize_images.php
 */

declare(strict_types=1);

$root = dirname(__DIR__);
$htmlFiles = [
    $root . '/Index.html',
    $root . '/facilities.html',
    $root . '/productions.html',
    $root . '/contact.html',
];

$optimizedAvifDir = $root . '/img/optimized-avif';

if (!is_dir($optimizedAvifDir) && !mkdir($optimizedAvifDir, 0755, true) && !is_dir($optimizedAvifDir)) {
    fwrite(STDERR, "No se pudo crear el directorio: {$optimizedAvifDir}\n");
    exit(1);
}

preg_match_all('/<img[^>]*src="([^"]+)"/i', implode("\n", array_map('file_get_contents', $htmlFiles)), $matches);
$sources = array_unique($matches[1]);

$canCreateAvif = function_exists('imageavif');
if (!$canCreateAvif) {
    fwrite(STDOUT, "Aviso: imageavif() no está disponible.\n");
}

$avifConverted = 0;
$skipped = 0;

foreach ($sources as $src) {
    if (str_starts_with($src, 'http') || $src === '') {
        continue;
    }

    $sourcePath = $root . '/' . ltrim($src, '/');
    if (!is_file($sourcePath)) {
        $skipped++;
        fwrite(STDOUT, "Skip (no existe): {$src}\n");
        continue;
    }

    if (!$canCreateAvif) {
        continue;
    }

    $extension = strtolower(pathinfo($sourcePath, PATHINFO_EXTENSION));
    $basename = pathinfo($src, PATHINFO_FILENAME);
    $relativeDir = dirname($src);

    $image = null;
    if ($extension === 'jpg' || $extension === 'jpeg') {
        $image = @imagecreatefromjpeg($sourcePath);
    } elseif ($extension === 'png') {
        $image = @imagecreatefrompng($sourcePath);
        if ($image !== false) {
            imagepalettetotruecolor($image);
            imagealphablending($image, true);
            imagesavealpha($image, true);
        }
    }

    if ($image === false || $image === null) {
        $skipped++;
        fwrite(STDOUT, "Skip (formato no soportado para conversión): {$src}\n");
        continue;
    }

    $targetSubdir = $optimizedAvifDir . '/' . ($relativeDir === '.' ? '' : $relativeDir);
    if (!is_dir($targetSubdir)) {
        mkdir($targetSubdir, 0755, true);
    }
    $targetPath = rtrim($targetSubdir, '/') . '/' . $basename . '.avif';

    if (imageavif($image, $targetPath, 50)) {
        $avifConverted++;
    } else {
        $skipped++;
        fwrite(STDOUT, "Skip (falló conversión avif): {$src}\n");
    }

    imagedestroy($image);
}

fwrite(STDOUT, "\nResumen:\n");
fwrite(STDOUT, "- AVIF generados: {$avifConverted}\n");
fwrite(STDOUT, "- Elementos omitidos: {$skipped}\n");
