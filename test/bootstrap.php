<?php

require dirname(__DIR__) . '/scripts/setup-autoloading.php';

spl_autoload_register(function ($class) {
    $class = ltrim($class, '\\');
    $file = __DIR__ . '/' . strtr($class, '_\\', '//') . '.php';
    is_readable($file) && (require $file);
});
