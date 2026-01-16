<?php

if (!defined('ABSPATH')) {
    exit;
}

class ODC25_Autoloader
{
    public static function register(): void
    {
        spl_autoload_register([self::class, 'autoload']);
    }

    public static function autoload(string $class): void
    {
        if (strpos($class, 'ODC25_') !== 0) {
            return;
        }

        $normalized = strtolower(str_replace('ODC25_', '', $class));
        $file = ODC25_PATH . 'includes/class-odc25-' . str_replace('_', '-', $normalized) . '.php';

        if (file_exists($file)) {
            require_once $file;
        }
    }
}
