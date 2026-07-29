<?php

namespace Application\Env;

class EnvService implements EnvServiceInterface
{

    private array $env = [];


    public function __construct()
    {
        $path = __DIR__ . '/../../.env';
        if (!file_exists($path)) return;
    
        $lines = file($path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    
        foreach ($lines as $line) {
            if (strpos(trim($line), '#') === 0) continue;
            list($key, $value) = explode('=', $line, 2);
            putenv(trim($key) . '=' . trim($value));
            $this->env[trim($key)] = trim($value);
        }

    }


    public function get(string $key)
    {
        return $_ENV[$key];
    }
}