<?php

namespace loader;

class Psr4ClassLoader
{
    private $prefix;
    private $baseDir;

    public function __construct($prefix, $baseDir)
    {
        $this->prefix = $prefix;
        $this->baseDir = rtrim($baseDir, '/\\') . DIRECTORY_SEPARATOR;
    }

    public function register()
    {
        spl_autoload_register(array($this, 'loadClass'));
    }

    public function loadClass($className)
    {
        $len = strlen($this->prefix);
        if (strncmp($this->prefix, $className, $len) !== 0) {
            return;
        }

        $relativeClass = substr($className, $len);

        $file = $this->baseDir . str_replace('\\', DIRECTORY_SEPARATOR, $relativeClass) . '.php';

        if (file_exists($file)) {
            require_once $file;
        }
    }
}