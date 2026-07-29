<?php 
namespace Presentation\Http\Controllers;

use R2Packages\Framework\Infrastructure\Framework\Env\EnvServiceInterface;

class TestController
{
    private EnvServiceInterface $envService;

    public function __construct(EnvServiceInterface $envService)
    {
        $this->envService = $envService;
    }

    public function index()
    {
        echo 'Hello World: ' . $this->envService->get('APP_NAME') . '<br>';
        echo 'DB_HOST: ' . $this->envService->get('DB_HOST') . '<br>';
        echo 'DB_NAME: ' . $this->envService->get('DB_NAME') . '<br>';
        echo 'DB_USER: ' . $this->envService->get('DB_USER') . '<br>';
        echo 'DB_PASSWORD: ' . $this->envService->get('DB_PASSWORD') . '<br>';
    }
}