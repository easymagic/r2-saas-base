<?php

namespace Presentation\View;

class View
{
    /** @var string */
    private static $basePath = '';

    public static function setBasePath($path)
    {
        self::$basePath = rtrim($path, '/\\');
    }

    public static function basePath()
    {
        if (self::$basePath !== '') {
            return self::$basePath;
        }
        return dirname(dirname(__DIR__)) . '/views';
    }

    /**
     * Render a view (relative to views/), optionally wrapped in a layout.
     *
     * @param string $view e.g. 'auth/login' or 'dashboard/index'
     * @param array $data
     * @param string|null $layout e.g. 'layouts/app' or null for no layout
     */
    public static function render($view, array $data = [], $layout = 'layouts/app')
    {
        $content = self::capture($view, $data);
        if ($layout === null || $layout === '') {
            echo $content;
            return;
        }
        $data['content'] = $content;
        echo self::capture($layout, $data);
    }

    public static function partial($view, array $data = [])
    {
        echo self::capture($view, $data);
    }

    private static function capture($view, array $data)
    {
        $file = self::basePath() . '/' . str_replace('.', '/', $view) . '.php';
        if (!is_file($file)) {
            throw new \RuntimeException('View not found: ' . $view);
        }
        extract($data, EXTR_SKIP);
        ob_start();
        require $file;
        return ob_get_clean();
    }
}
