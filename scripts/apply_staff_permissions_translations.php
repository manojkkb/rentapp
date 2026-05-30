<?php

declare(strict_types=1);

/**
 * Apply resources/lang/data/staff_permissions_translations.php to each locale vendor.php.
 */
$root = dirname(__DIR__);
$en = require $root.'/resources/lang/en/vendor.php';
$translations = require $root.'/resources/lang/data/staff_permissions_translations.php';

function writeVendorPhp(string $path, array $data): void
{
    $lines = ["<?php\n", "\n", 'return [', "\n"];
    foreach ($data as $key => $value) {
        $lines[] = '    '.var_export($key, true).' => '.var_export($value, true).",\n";
    }
    $lines[] = "];\n";
    file_put_contents($path, implode('', $lines));
}

foreach ($translations as $locale => $patch) {
    $vendorPath = $root.'/resources/lang/'.$locale.'/vendor.php';
    if (! is_file($vendorPath)) {
        fwrite(STDERR, "Skip $locale: no vendor.php\n");

        continue;
    }
    $v = require $vendorPath;
    foreach ($patch as $k => $val) {
        $v[$k] = $val;
    }
    $merged = array_merge($en, $v);
    writeVendorPhp($vendorPath, $merged);
    echo $locale.': applied '.count($patch)." staff permission strings\n";
}
