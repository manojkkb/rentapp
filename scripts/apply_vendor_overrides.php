<?php

declare(strict_types=1);

/**
 * Apply resources/lang/data/{locale}_vendor_overrides.php onto resources/lang/{locale}/vendor.php
 * (merged with en key order). Add new override files as needed.
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

foreach (glob($root.'/resources/lang/data/*_vendor_overrides.php') ?: [] as $patchPath) {
    if (! preg_match('#[/\\\\]([a-z]{2})_vendor_overrides\\.php$#', $patchPath, $m)) {
        continue;
    }
    $code = $m[1];
    if ($code === 'en') {
        continue;
    }
    $vendorPath = $root.'/resources/lang/'.$code.'/vendor.php';
    if (! is_file($vendorPath)) {
        fwrite(STDERR, "Skip $code: no vendor.php\n");

        continue;
    }
    $patch = require $patchPath;
    $v = require $vendorPath;
    foreach ($patch as $k => $val) {
        $v[$k] = $val;
    }
    $merged = array_merge($en, $v);
    writeVendorPhp($vendorPath, $merged);
    echo $code.': applied '.count($patch)." overrides\n";
}
