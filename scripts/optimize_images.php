<?php
/**
 * Optimiza imágenes generando WebP cuando la extensión GD lo permite.
 *
 * - Genera .webp en: img/optimized-webp/ (si imagewebp está disponible)
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

$optimizedWebpDir = $root . '/img/optimized-webp';

if (!is_dir($optimizedWebpDir) && !mkdir($optimizedWebpDir, 0755, true) && !is_dir($optimizedWebpDir)) {
    fwrite(STDERR, "No se pudo crear el directorio: {$optimizedWebpDir}\n");
    exit(1);
}

preg_match_all('/<img[^>]*src="([^"]+)"/i', implode("\n", array_map('file_get_contents', $htmlFiles)), $matches);
$sources = array_unique($matches[1]);

$canCreateWebp = function_exists('imagewebp');
if (!$canCreateWebp) {
    fwrite(STDOUT, "Aviso: imagewebp() no está disponible.\n");
}

$webpConverted = 0;
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

    if (!$canCreateWebp) {
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

    $targetSubdir = $optimizedWebpDir . '/' . ($relativeDir === '.' ? '' : $relativeDir);
    if (!is_dir($targetSubdir)) {
        mkdir($targetSubdir, 0755, true);
    }
    $targetPath = rtrim($targetSubdir, '/') . '/' . $basename . '.webp';

    if (imagewebp($image, $targetPath, 82)) {
        $webpConverted++;
    } else {
        $skipped++;
        fwrite(STDOUT, "Skip (falló conversión webp): {$src}\n");
    }

    imagedestroy($image);
}

fwrite(STDOUT, "\nResumen:\n");
fwrite(STDOUT, "- WebP generados: {$webpConverted}\n");
fwrite(STDOUT, "- Elementos omitidos: {$skipped}\n");
