<?php

/**
 * Classic PHP UI helpers (loaded from Kernel/boot_extend.php).
 */

if (!function_exists('web_base_path')) {
    function web_base_path()
    {
        static $base = null;
        if ($base !== null) {
            return $base;
        }
        $env = getenv('WEB_BASE_PATH');
        if ($env !== false && $env !== '') {
            $base = rtrim(str_replace('\\', '/', $env), '/');
            return $base;
        }
        $script = isset($_SERVER['SCRIPT_NAME']) ? str_replace('\\', '/', $_SERVER['SCRIPT_NAME']) : '';
        // PHP built-in server often sets SCRIPT_NAME to the request path (e.g. /cart/add),
        // which must not be treated as the app base or redirects become /cart/shop.
        if ($script === '' || substr($script, -4) !== '.php') {
            $base = '';
            return $base;
        }
        $dir = dirname($script);
        if ($dir === '/' || $dir === '\\' || $dir === '.' || $dir === '') {
            $base = '';
        } else {
            $base = rtrim($dir, '/');
        }
        return $base;
    }
}

if (!function_exists('web_url')) {
    function web_url($path = '/')
    {
        $base = web_base_path();
        $path = '/' . ltrim((string) $path, '/');
        if ($path === '/') {
            return $base === '' ? '/' : $base . '/';
        }
        return $base . $path;
    }
}

if (!function_exists('upload_url')) {
    function upload_url($path)
    {
        $path = (string) $path;
        if ($path === '') {
            return '';
        }
        if (preg_match('#^https?://#i', $path)) {
            return $path;
        }
        return web_url(ltrim($path, '/'));
    }
}

if (!function_exists('e')) {
    function e($value)
    {
        return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
    }
}

if (!function_exists('format_naira')) {
    function format_naira($amount)
    {
        $n = (float) $amount;
        return '₦' . number_format($n, 2);
    }
}

if (!function_exists('format_dollar')) {
    function format_dollar($amount)
    {
        $n = (float) $amount;
        return '$' . number_format($n, 2);
    }
}

if (!function_exists('format_date')) {
    function format_date($value)
    {
        if ($value === null || $value === '') {
            return '—';
        }
        $ts = strtotime(str_replace(' ', 'T', (string) $value));
        if ($ts === false) {
            return (string) $value;
        }
        return date('M j, Y', $ts);
    }
}

if (!function_exists('user_initials')) {
    function user_initials($name)
    {
        $name = trim((string) $name);
        if ($name === '') {
            return '?';
        }
        $parts = preg_split('/\s+/', $name);
        $first = isset($parts[0][0]) ? strtoupper($parts[0][0]) : '';
        $second = isset($parts[1][0]) ? strtoupper($parts[1][0]) : '';
        return $first . $second;
    }
}

if (!function_exists('nav_active')) {
    function nav_active($current, $match)
    {
        return $current === $match
            ? 'rounded-xl bg-gray-100 px-3 py-2 text-sm font-medium text-blue-900'
            : 'rounded-xl px-3 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50';
    }
}

if (!function_exists('status_badge_class')) {
    function status_badge_class($status)
    {
        $s = strtolower((string) $status);
        if (in_array($s, ['approved', 'paid', 'delivered', 'success'], true)) {
            return 'bg-green-100 text-green-800';
        }
        if (in_array($s, ['rejected', 'cancelled', 'failed'], true)) {
            return 'bg-red-100 text-red-800';
        }
        if (in_array($s, ['pending', 'processing', 'in_transit', 'shipped'], true)) {
            return 'bg-yellow-100 text-yellow-800';
        }
        return 'bg-gray-100 text-gray-700';
    }
}
