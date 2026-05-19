<?php

/**
 * Genera iconos PWA en PNG puro (sin GD).
 * Ejecutar: php public/icons/generate.php
 */

function createMinimalPNG(int $size, string $outputPath): void
{
    $bg    = [0x08, 0x09, 0x10];
    $color = [0x7C, 0x5C, 0xBF];

    $pixels = [];
    $margin = (int) ($size * 0.12);

    for ($y = 1; $y <= $size; $y++) {
        $row = [];
        for ($x = 1; $x <= $size; $x++) {
            $inBox = $x >= $margin && $x <= $size - $margin
                  && $y >= $margin && $y <= $size - $margin;
            if ($inBox) {
                $row[] = chr(0) . chr($color[0]) . chr($color[1]) . chr($color[2]);
            } else {
                $row[] = chr(0) . chr($bg[0]) . chr($bg[1]) . chr($bg[2]);
            }
        }
        $pixels[] = chr(0) . implode('', $row);
    }

    $raw = implode('', $pixels);

    $signature = "\x89PNG\r\n\x1a\n";

    $ihdr = pack('N', 13) . 'IHDR'
          . pack('N', $size) . pack('N', $size)
          . chr(8) . chr(6) . chr(0) . chr(0) . chr(0);
    $ihdr .= pack('N', crc32(substr($ihdr, 4)));

    $idatData = zlib_encode($raw, ZLIB_ENCODING_DEFLATE);
    $idat = pack('N', strlen($idatData)) . 'IDAT' . $idatData;
    $idat .= pack('N', crc32(substr($idat, 4)));

    $iend = pack('N', 0) . 'IEND';
    $iend .= pack('N', crc32('IEND'));

    file_put_contents($outputPath, $signature . $ihdr . $idat . $iend);
}

createMinimalPNG(192, __DIR__ . '/icon-192.png');
createMinimalPNG(512, __DIR__ . '/icon-512.png');

echo "Iconos PWA generados correctamente.\n";
