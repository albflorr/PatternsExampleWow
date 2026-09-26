<?php
spl_autoload_register(function (string $class): void {
    $prefix = 'AzerothEngine\\';
    $baseDir = __DIR__ . '/src/';

    $len = strlen($prefix);
    if (strncmp($prefix, $class, $len) !== 0) {
        echo "Not our class: $class\n";
        return;
    }

    $relativeClass = substr($class, $len);
    $file = $baseDir . str_replace('\\', '/', $relativeClass) . '.php';

    echo "Looking for: $file\n";
    if (file_exists($file)) {
        echo "Found!\n";
        require_once $file;
    } else {
        echo "NOT FOUND!\n";
    }
});

$f = new \AzerothEngine\Creational\Factory\HordeCharacterFactory();
echo "OK\n";