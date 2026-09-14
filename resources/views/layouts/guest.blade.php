<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ config('app.name', 'Task Manager') }}</title>

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,500,600,700&display=swap" rel="stylesheet" />

        <!-- Scripts -->
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="font-sans antialiased" style="background-color: var(--color-canvas, #EDF2F4); color: var(--color-dark, #2B2D42);">
        <div class="min-vh-100 d-flex flex-column justify-content-center align-items-center py-5 px-3">
            <div class="mb-4 text-center">
                <a href="/" class="text-decoration-none d-inline-flex align-items-center gap-2.5">
                    <div class="w-9 h-9 rounded-3 bg-primary text-white d-flex align-items-center justify-center shadow-sm">
                        <x-iconly name="tick-square" class="w-5 h-5" />
                    </div>
                    <span class="fs-4 fw-bold text-dark tracking-tight">Task Manager</span>
                </a>
            </div>

            <div class="w-100 max-w-md">
                <div class="card border-0 shadow-sm rounded-4 overflow-hidden bg-white p-4 p-sm-5">
                    {{ $slot }}
                </div>
            </div>

            <div class="mt-4 text-center text-secondary small">
                &copy; {{ date('Y') }} Task Manager. All rights reserved.
            </div>
        </div>
    </body>
</html>

