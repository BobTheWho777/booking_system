<?php
declare(strict_types=1);

if (!defined('BASE_URL')) {
    $scriptName = str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME'] ?? ''));
    $baseUrl = preg_replace('#/+(admin|api)$#', '', $scriptName);

    define('BASE_URL', rtrim($baseUrl ?: '/', '/') . '/');
}

if (!function_exists('app_url')) {
    function app_url(string $path = ''): string
    {
        return BASE_URL . ltrim($path, '/');
    }
}

if (!function_exists('app_redirect')) {
    function app_redirect(string $path): void
    {
        header('Location: ' . app_url($path));
        exit;
    }
}
