<?php
declare(strict_types=1);

/**
 * Autoloader PSR-4 Natif (Sans Composer)
 * Mappe le préfixe de namespace 'App\' vers le répertoire 'src/'.
 */

require_once __DIR__ . '/database.php';

spl_autoload_register(function (string $class) {
    $prefix = 'App\\';
    $baseDir = __DIR__ . '/../src/';

    $len = strlen($prefix);
    if (strncmp($prefix, $class, $len) !== 0) {
        return;
    }

    $relativeClass = substr($class, $len);
    $file = $baseDir . str_replace('\\', '/', $relativeClass) . '.php';

    if (file_exists($file)) {
        require_once $file;
    }
});
