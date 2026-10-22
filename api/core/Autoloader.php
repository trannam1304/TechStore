<?php
/**
 * TechStore - PSR-4 Compliant Autoloader
 * Automatically resolves and loads classes in Api\ and Config\ namespaces
 */

namespace Api\Core;

class Autoloader {
    private static array $prefixes = [];

    public static function register(): void {
        $baseDir = dirname(__DIR__, 2);

        self::addNamespace('Config\\', $baseDir . '/config/');
        self::addNamespace('Api\\', $baseDir . '/api/');

        spl_autoload_register([__CLASS__, 'loadClass']);
    }

    public static function addNamespace(string $prefix, string $baseDir): void {
        $prefix = trim($prefix, '\\') . '\\';
        $baseDir = rtrim($baseDir, DIRECTORY_SEPARATOR) . '/';
        self::$prefixes[$prefix] = $baseDir;
    }

    public static function loadClass(string $class): bool {
        foreach (self::$prefixes as $prefix => $baseDir) {
            if (str_starts_with($class, $prefix)) {
                $relativeClass = substr($class, strlen($prefix));
                $parts = explode('\\', $relativeClass);
                
                $className = array_pop($parts);
                $subDir = '';
                if (!empty($parts)) {
                    $subDir = strtolower(implode('/', $parts)) . '/';
                }

                $file = $baseDir . $subDir . $className . '.php';

                if (file_exists($file)) {
                    require_once $file;
                    return true;
                }

                // Fallback: check exact case
                $exactFile = $baseDir . str_replace('\\', '/', $relativeClass) . '.php';
                if (file_exists($exactFile)) {
                    require_once $exactFile;
                    return true;
                }
            }
        }
        return false;
    }
}
