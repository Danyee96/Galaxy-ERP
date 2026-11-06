<?php
// Runs without vendor/ so a fresh Composer install can report useful failures.
$lock = json_decode(file_get_contents(__DIR__.'/../composer.lock'), true, 512, JSON_THROW_ON_ERROR);
$required = $provided = [];
foreach ($lock['packages'] as $package) {
    foreach (array_keys($package['require'] ?? []) as $name) {
        if (strpos($name, 'ext-') === 0) $required[$name] = true;
    }
    foreach (array_keys(array_merge($package['provide'] ?? [], $package['replace'] ?? [])) as $name) {
        if (strpos($name, 'ext-') === 0) $provided[$name] = true;
    }
}
$missing = [];
foreach (array_diff_key($required, $provided) as $name => $_) {
    if (!extension_loaded(substr($name, 4))) $missing[] = $name;
}
sort($missing);
$errors = [];
if (PHP_VERSION_ID < 80200 || PHP_VERSION_ID >= 80300) {
    $errors[] = 'Build PHP is '.PHP_VERSION.'; Galaxy ERP requires PHP 8.2.x. Set Hostinger BUILD and WEBSITE PHP versions to 8.2.';
}
if ($missing) $errors[] = 'Enable these PHP extensions for the build: '.implode(', ', $missing).'.';
$message = 'Galaxy ERP platform: PHP '.PHP_VERSION.'; missing extensions: '.($missing ? implode(', ', $missing) : 'none').PHP_EOL;
fwrite(STDERR, $message);
if ($errors) {
    foreach ($errors as $error) fwrite(STDERR, 'DEPLOYMENT BLOCKED: '.$error.PHP_EOL);
    // One explicit failure; never bypass incompatible PHP or missing extensions.
    exit(1);
}
fwrite(STDERR, 'Galaxy ERP deployment platform check passed.'.PHP_EOL);
