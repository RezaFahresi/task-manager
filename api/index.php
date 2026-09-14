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
    }
}

// 2. Delegate to public/index.php
require __DIR__.'/../public/index.php';
