<?php
declare(strict_types=1);

if (!defined('BASE_URL')) {
    $documentRoot = realpath($_SERVER['DOCUMENT_ROOT'] ?? '');
    $projectRoot = realpath(__DIR__ . '/..');

    $baseUrl = '/';
    if ($documentRoot !== false && $projectRoot !== false) {
        $documentRoot = str_replace('\\', '/', rtrim($documentRoot, '/'));
        $projectRoot = str_replace('\\', '/', $projectRoot);

        if (strpos($projectRoot, $documentRoot) === 0) {
            $relative = substr($projectRoot, strlen($documentRoot));
            $baseUrl = '/' . ltrim($relative, '/');
            $baseUrl = rtrim($baseUrl, '/') . '/';
        }
    }

    define('BASE_URL', $baseUrl);
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
