<?php

/**
 * Vercel Serverless Function Entry Point for Laravel
 */

// 1. Prepare ephemeral writable storage directories in /tmp
if (isset($_ENV['VERCEL']) || isset($_SERVER['VERCEL']) || getenv('VERCEL')) {
    $storageDirs = [
        '/tmp/storage/framework/views',
        '/tmp/storage/framework/cache/data',
        '/tmp/storage/framework/sessions',
        '/tmp/storage/logs',
        '/tmp/storage/app/public',
        '/tmp/bootstrap/cache',
    ];

    foreach ($storageDirs as $dir) {
        if (! is_dir($dir)) {
            @mkdir($dir, 0755, true);
        }
    }

    if (! getenv('VIEW_COMPILED_PATH')) {
        putenv('VIEW_COMPILED_PATH=/tmp/storage/framework/views');
        $_ENV['VIEW_COMPILED_PATH'] = $_SERVER['VIEW_COMPILED_PATH'] = '/tmp/storage/framework/views';
    }

    if (! getenv('APP_SERVICES_CACHE')) {
        putenv('APP_SERVICES_CACHE=/tmp/bootstrap/cache/services.php');
        $_ENV['APP_SERVICES_CACHE'] = $_SERVER['APP_SERVICES_CACHE'] = '/tmp/bootstrap/cache/services.php';
    }

    if (! getenv('APP_PACKAGES_CACHE')) {
        putenv('APP_PACKAGES_CACHE=/tmp/bootstrap/cache/packages.php');
        $_ENV['APP_PACKAGES_CACHE'] = $_SERVER['APP_PACKAGES_CACHE'] = '/tmp/bootstrap/cache/packages.php';
    }

    if (! getenv('APP_CONFIG_CACHE')) {
        putenv('APP_CONFIG_CACHE=/tmp/bootstrap/cache/config.php');
        $_ENV['APP_CONFIG_CACHE'] = $_SERVER['APP_CONFIG_CACHE'] = '/tmp/bootstrap/cache/config.php';
    }

    if (! getenv('APP_ROUTES_CACHE')) {
        putenv('APP_ROUTES_CACHE=/tmp/bootstrap/cache/routes.php');
        $_ENV['APP_ROUTES_CACHE'] = $_SERVER['APP_ROUTES_CACHE'] = '/tmp/bootstrap/cache/routes.php';
    }

    if (! getenv('APP_EVENTS_CACHE')) {
        putenv('APP_EVENTS_CACHE=/tmp/bootstrap/cache/events.php');
        $_ENV['APP_EVENTS_CACHE'] = $_SERVER['APP_EVENTS_CACHE'] = '/tmp/bootstrap/cache/events.php';
    }
}

// 2. Delegate to public/index.php
require __DIR__.'/../public/index.php';
