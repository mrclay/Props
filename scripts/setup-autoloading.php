<?php

if (defined('Props\\AUTOLOADER_ADDED')) {
    return;
}

spl_autoload_register(function ($class) {
    $class = ltrim($class, '\\');
    if (0 !== strpos($class, 'Props\\')) {
        return;
    }
    $file = dirname(__DIR__) . '/src/' . strtr($class, '_\\', '//') . '.php';
    is_readable($file) && (require $file);
});

define('Props\\AUTOLOADER_ADDED', true);
