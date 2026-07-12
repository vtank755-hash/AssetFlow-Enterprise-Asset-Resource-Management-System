<?php
/**
 * Application Autoloader (PSR-4 compliant)
 * Acts as an fallback/independent autoloading engine for Class mapping.
 */

spl_autoload_register(function ($class) {
    // Project-specific namespace prefix
    $prefix = 'App\\';
    
    // Base directory for the namespace prefix
    $baseDir = dirname(__DIR__) . '/app/';
    
    // Check if the class uses the namespace prefix
    $len = strlen($prefix);
    if (strncmp($prefix, $class, $len) !== 0) {
        // Move to next registered autoloader
        return;
    }
    
    // Get the relative class name
    $relativeClass = substr($class, $len);
    
    // Replace the namespace prefix with the base directory, replace namespace
    // separators with directory separators, append with .php
    $file = $baseDir . str_replace('\\', '/', $relativeClass) . '.php';
    
    // If the file exists, load it
    if (file_exists($file)) {
        require $file;
    }
});
