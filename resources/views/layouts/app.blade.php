<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ $title ?? config('app.name', 'Task Manager') }}</title>

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,500,600,700&display=swap" rel="stylesheet" />

        <!-- Scripts & Styles -->
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body style="background-color: #EDF2F4; color: #2B2D42; font-family: 'Figtree', system-ui, sans-serif;">
        <div x-data="{ sidebarOpen: false }" class="d-flex min-vh-100 position-relative">
            <!-- Mobile Drawer Backdrop -->
            <div
                x-show="sidebarOpen"
                x-transition:enter="transition-opacity ease-linear duration-150"
                x-transition:enter-start="opacity-0"
                x-transition:enter-end="opacity-100"
                x-transition:leave="transition-opacity ease-linear duration-150"
                x-transition:leave-start="opacity-100"
                x-transition:leave-end="opacity-0"
                @click="sidebarOpen = false"
                class="position-fixed top-0 start-0 w-100 h-100 d-md-none"
                style="background-color: rgba(43, 45, 66, 0.4); z-index: 1040; backdrop-filter: blur(2px);"
            ></div>

            <!-- Mobile Drawer Panel -->
            <div
                x-show="sidebarOpen"
                x-transition:enter="transition ease-in-out duration-200 transform"
                x-transition:enter-start="-translate-x-full"
                x-transition:enter-end="translate-x-0"
                x-transition:leave="transition ease-in-out duration-200 transform"
                x-transition:leave-start="translate-x-0"
                x-transition:leave-end="-translate-x-full"
                class="position-fixed top-0 bottom-0 start-0 bg-white border-end d-flex flex-column d-md-none"
                style="width: 250px; z-index: 1050; border-color: #DFE5EC !important;"
            >
                <div class="d-flex align-items-center justify-content-between px-3 border-bottom" style="height: 60px; border-color: #DFE5EC !important;">
                    <a href="{{ route('dashboard') }}" class="d-flex align-items-center gap-2 text-decoration-none" style="color: #2B2D42;">
                        <span class="d-inline-flex align-items-center justify-content-center rounded-2" style="width: 28px; height: 28px; background-color: #EEF2FF; color: #4361EE;">
                            <x-iconly name="tick-square" size="18" />
                        </span>
                        <span class="fw-bold tracking-tight" style="font-size: 15px;">Task Manager</span>
                    </a>
                    <button
                        type="button"
                        @click="sidebarOpen = false"
                        class="btn btn-sm p-1 rounded-2 border-0"
                        style="color: #8D99AE;"
                        aria-label="Tutup Menu"
                    >
                        <x-iconly name="close" size="18" />
                    </button>
                </div>
                @include('layouts.sidebar-nav')
            </div>

            <!-- Desktop Sidebar (Fixed) -->
            <aside
                class="d-none d-md-flex flex-column position-fixed top-0 bottom-0 start-0 bg-white border-end"
                style="width: 250px; z-index: 1020; border-color: #DFE5EC !important;"
            >
                <!-- Brand Header -->
                <div class="d-flex align-items-center px-4 border-bottom" style="height: 60px; border-color: #DFE5EC !important;">
                    <a href="{{ route('dashboard') }}" class="d-flex align-items-center gap-2 text-decoration-none" style="color: #2B2D42;">
                        <span class="d-inline-flex align-items-center justify-content-center rounded-2" style="width: 30px; height: 30px; background-color: #EEF2FF; color: #4361EE;">
                            <x-iconly name="tick-square" size="19" />
                        </span>
                        <span class="fw-bold tracking-tight" style="font-size: 15px;">Task Manager</span>
                    </a>
                </div>
                @include('layouts.sidebar-nav')
            </aside>

            <!-- Main Layout Content Column -->
            <div class="flex-grow-1 d-flex flex-column min-vh-100" style="margin-left: 0; padding-left: 0;">
                <div class="d-none d-md-block" style="width: 250px; flex-shrink: 0; float: left; height: 1px;"></div>

                <div class="w-100" style="padding-left: 0;">
                    <div class="d-none d-md-block" style="padding-left: 250px;">
                        <!-- Desktop Header -->
                    </div>
                </div>

                <div class="flex-grow-1 d-flex flex-column w-100" style="margin-left: 0;">
                    <div style="margin-left: 0;" class="desktop-main-wrapper">
                        <!-- Global Sticky Header -->
                        @php
                            $sidebarUnreadCount = $unreadNotificationCount ?? 0;
                            $breadcrumb = match(true) {
                                request()->routeIs('dashboard') => 'Dashboard',
                                request()->routeIs('tasks.create') => 'Tasks / Tambah',
                                request()->routeIs('tasks.edit') => 'Tasks / Edit',
                                request()->routeIs('tasks.show') => 'Tasks / Detail',
                                request()->routeIs('tasks.*') => 'Tasks',
                                request()->routeIs('categories.create') => 'Categories / Tambah',
                                request()->routeIs('categories.edit') => 'Categories / Edit',
                                request()->routeIs('categories.*') => 'Categories',
                                request()->routeIs('notifications.*') => 'Notifications',
                                request()->routeIs('profile.*') => 'Profile',
                                default => 'Workspace',
                            };
                        @endphp

                        <header
                            class="sticky-top bg-white border-bottom px-3 px-md-4 px-lg-5 d-flex align-items-center justify-content-between"
                            style="height: 60px; border-color: #DFE5EC !important; z-index: 1010; box-shadow: 0 1px 2px rgba(43, 45, 66, 0.02);"
                        >
                            <!-- Left: Mobile Menu Toggle + Breadcrumb -->
                            <div class="d-flex align-items-center gap-2 gap-md-3">
                                <button
                                    type="button"
                                    @click="sidebarOpen = true"
                                    class="d-md-none btn btn-sm p-1.5 rounded-2 border-0"
                                    style="color: #2B2D42; background-color: #F8FAFC;"
                                    aria-label="Buka Menu"
                                >
                                    <x-iconly name="menu" size="20" />
                                </button>

                                <div class="d-flex align-items-center gap-2">
                                    <a href="{{ route('dashboard') }}" class="d-md-none d-flex align-items-center gap-1.5 text-decoration-none fw-bold small" style="color: #2B2D42;">
                                        <span class="rounded-circle d-inline-block" style="width: 7px; height: 7px; background-color: #4361EE;"></span>
                                        <span>Task Manager</span>
                                    </a>
                                    <span class="d-md-none text-muted" style="font-size: 12px;">/</span>
                                    <nav class="d-flex align-items-center gap-1.5 small" aria-label="breadcrumb">
                                        <span class="d-none d-md-inline fw-medium" style="color: #8D99AE;">Workspace</span>
                                        <span class="d-none d-md-inline" style="color: #DFE5EC;">/</span>
                                        <span class="fw-semibold" style="color: #2B2D42;">{{ $breadcrumb }}</span>
                                    </nav>
                                </div>
                            </div>

                            <!-- Right: Notifications + Profile Dropdown -->
                            <div class="d-flex align-items-center gap-2 gap-sm-3">
                                <!-- Notifications Bell -->
                                <a
                                    href="{{ route('notifications.index') }}"
                                    class="btn btn-sm position-relative p-2 rounded-2 border-0 d-inline-flex align-items-center justify-content-center"
                                    style="color: #8D99AE; background: #F8FAFC;"
                                    title="Notifications"
                                    onmouseover="this.style.color='#4361EE';"
                                    onmouseout="this.style.color='#8D99AE';"
                                >
                                    <x-iconly name="notification" size="18" />
                                    @if ($sidebarUnreadCount > 0)
                                        <span
                                            class="position-absolute top-0 start-100 translate-middle p-1 rounded-circle border border-white"
                                            style="background-color: #EF233C;"
                                        >
                                            <span class="visually-hidden">New alerts</span>
                                        </span>
                                    @endif
                                </a>

                                <div class="vr" style="height: 20px; color: #DFE5EC;"></div>

                                <!-- Profile Dropdown -->
                                @auth
                                    <div class="dropdown" x-data="{ userMenuOpen: false }" @click.outside="userMenuOpen = false">
                                        <button
                                            class="btn btn-sm d-flex align-items-center gap-2 p-1 pe-2 rounded-2 border-0"
                                            type="button"
                                            @click="userMenuOpen = !userMenuOpen"
                                            style="background-color: #F8FAFC;"
                                        >
                                            <div
                                                class="rounded-circle d-flex align-items-center justify-content-center text-white fw-semibold"
                                                style="width: 28px; height: 28px; background-color: #4361EE; font-size: 11px;"
                                            >
                                                {{ strtoupper(substr(Auth::user()->name, 0, 1)) }}
                                            </div>
                                            <span class="d-none d-sm-inline fw-medium text-truncate small" style="color: #2B2D42; max-width: 130px;">
                                                {{ Auth::user()->name }}
                                            </span>
                                            <x-iconly name="chevron-down" size="13" style="color: #8D99AE;" />
                                        </button>

                                        <ul
                                            class="dropdown-menu dropdown-menu-end shadow-sm border p-1"
                                            :class="{ 'show': userMenuOpen }"
                                            style="border-color: #DFE5EC !important; min-width: 200px;"
                                        >
                                            <li class="px-3 py-2 border-bottom" style="border-color: #DFE5EC !important;">
                                                <div class="fw-semibold text-truncate small" style="color: #2B2D42;">{{ Auth::user()->name }}</div>
                                                <div class="text-truncate" style="color: #8D99AE; font-size: 11px;">{{ Auth::user()->email }}</div>
                                            </li>
                                            <li>
                                                <a class="dropdown-item d-flex align-items-center gap-2 py-2 rounded-1 small" href="{{ route('profile.edit') }}" style="color: #2B2D42;">
                                                    <x-iconly name="profile" size="15" style="color: #8D99AE;" />
                                                    <span>Profile Settings</span>
                                                </a>
                                            </li>
                                            <li>
                                                <a class="dropdown-item d-flex align-items-center justify-content-between py-2 rounded-1 small" href="{{ route('notifications.index') }}" style="color: #2B2D42;">
                                                    <div class="d-flex align-items-center gap-2">
                                                        <x-iconly name="notification" size="15" style="color: #8D99AE;" />
                                                        <span>Notifications</span>
                                                    </div>
                                                    @if ($sidebarUnreadCount > 0)
                                                        <span class="badge rounded-pill" style="background-color: #4361EE; font-size: 10px;">
                                                            {{ $sidebarUnreadCount }}
                                                        </span>
                                                    @endif
                                                </a>
                                            </li>
                                            <li class="border-top my-1" style="border-color: #DFE5EC !important;"></li>
                                            <li>
                                                <form method="POST" action="{{ route('logout') }}">
                                                    @csrf
                                                    <button type="submit" class="dropdown-item d-flex align-items-center justify-content-between py-2 rounded-1 small text-danger">
                                                        <span>Keluar</span>
                                                        <x-iconly name="logout" size="14" />
                                                    </button>
                                                </form>
                                            </li>
                                        </ul>
                                    </div>
                                @endauth
                            </div>
                        </header>

                        <!-- Main Body Content Slot -->
                        <main class="flex-grow-1">
                            {{ $slot }}
                        </main>
                    </div>
                </div>
            </div>
        </div>

        <style>
            @media (min-width: 768px) {
                .desktop-main-wrapper {
                    margin-left: 250px !important;
                }
            }
        </style>
    </body>
</html>
