<?php

declare(strict_types=1);

$root = dirname(__DIR__);
$enPath = $root.'/resources/lang/en/vendor.php';
$en = require $enPath;

foreach (glob($root.'/resources/lang/*/vendor.php') ?: [] as $f) {
    if (str_contains($f, DIRECTORY_SEPARATOR.'en'.DIRECTORY_SEPARATOR)) {
        continue;
    }
    $loc = require $f;
    $missing = array_diff_key($en, $loc);
    $extra = array_diff_key($loc, $en);
    $code = basename(dirname($f));
    echo $code."\tmissing=".count($missing)."\textra=".count($extra).PHP_EOL;
    if (count($missing) > 0 && count($missing) <= 80) {
        echo '  keys: '.implode(', ', array_keys($missing)).PHP_EOL;
    }
}
