<?php

declare(strict_types=1);

/**
 * Merges resources/lang/en/vendor.php into each locale vendor.php:
 * array_merge(en, locale) — English keys/order preserved; locale overrides existing keys.
 */
$root = dirname(__DIR__);
$en = require $root.'/resources/lang/en/vendor.php';

function writeVendorPhp(string $path, array $data): void
{
    $lines = ["<?php\n", "\n", 'return [', "\n"];
    foreach ($data as $key => $value) {
        $lines[] = '    '.var_export($key, true).' => '.var_export($value, true).",\n";
    }
    $lines[] = "];\n";
    file_put_contents($path, implode('', $lines));
}

foreach (glob($root.'/resources/lang/*/vendor.php') ?: [] as $path) {
    if (basename(dirname($path)) === 'en') {
        continue;
    }
    $locale = require $path;
    $merged = array_merge($en, $locale);
    writeVendorPhp($path, $merged);
    echo basename(dirname($path)).': '.count($merged)." keys\n";
}
