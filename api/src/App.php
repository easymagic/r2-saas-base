<?php

namespace Api;

class App
{
    public function run()
    {
        header('Content-Type: application/json');

        $method = isset($_SERVER['REQUEST_METHOD']) ? $_SERVER['REQUEST_METHOD'] : 'GET';
        $uri = isset($_SERVER['REQUEST_URI']) ? $_SERVER['REQUEST_URI'] : '/';
        $path = parse_url($uri, PHP_URL_PATH);
        $path = $path !== null ? rtrim($path, '/') : '';

        if ($path === '' || $path === '/api') {
            $path = '/';
        }

        if ($method === 'GET' && ($path === '/' || $path === '/health')) {
            echo json_encode(array(
                'status' => 'ok',
                'service' => 'api',
                'version' => '0.1.0',
            ));
            return;
        }

        http_response_code(404);
        echo json_encode(array(
            'error' => 'Not Found',
            'path' => $path,
        ));
    }
}
