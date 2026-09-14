<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">
        @auth
            <meta name="user-id" content="{{ Auth::id() }}">
        @endauth

        <title>{{ $title ?? config('app.name', 'Task Manager') }}</title>

        <!-- Favicon -->
        <link rel="icon" type="image/png" href="{{ asset('images/task-manager-logo.png') }}">

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
                        <img
                            src="{{ asset('images/task-manager-logo.png') }}"
                            alt="{{ config('app.name', 'Task Manager') }}"
                            class="rounded-2"
                            style="width: 28px; height: 28px; object-fit: contain;"
                        />
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
                        <img
                            src="{{ asset('images/task-manager-logo.png') }}"
                            alt="{{ config('app.name', 'Task Manager') }}"
                            class="rounded-2"
                            style="width: 32px; height: 32px; object-fit: contain;"
                        />
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
                                <!-- Notifications Bell Dropdown -->
                                <div class="dropdown" x-data="{ notifDropdownOpen: false }" @click.outside="notifDropdownOpen = false">
                                    <button
                                        class="btn btn-sm position-relative p-2 rounded-2 border-0 d-inline-flex align-items-center justify-content-center"
                                        type="button"
                                        id="notificationDropdownButton"
                                        @click="notifDropdownOpen = !notifDropdownOpen"
                                        style="color: #8D99AE; background: #F8FAFC;"
                                        title="Notifications"
                                        aria-label="Notifikasi"
                                        onmouseover="this.style.color='#4361EE';"
                                        onmouseout="this.style.color='#8D99AE';"
                                    >
                                        <x-iconly name="notification" size="18" />
                                        <span
                                            id="header-notification-badge"
                                            class="header-unread-badge position-absolute top-0 start-100 translate-middle badge rounded-pill {{ $sidebarUnreadCount > 0 ? '' : 'd-none' }}"
                                            style="background-color: #EF233C; font-size: 9px; padding: 2px 5px; transform: translate(-30%, -20%) !important;"
                                        >
                                            <span id="header-unread-count-text">{{ $sidebarUnreadCount > 99 ? '99+' : $sidebarUnreadCount }}</span>
                                        </span>
                                    </button>

                                    <div
                                        class="dropdown-menu dropdown-menu-end shadow-sm border p-0 mt-2"
                                        :class="{ 'show': notifDropdownOpen }"
                                        style="border-color: #DFE5EC !important; min-width: 320px; max-width: 380px; z-index: 1060; border-radius: 12px; overflow: hidden;"
                                    >
                                        <!-- Dropdown Header -->
                                        <div class="d-flex align-items-center justify-content-between px-3 py-2.5 bg-light border-bottom" style="border-color: #DFE5EC !important;">
                                            <div class="d-flex align-items-center gap-2">
                                                <span class="fw-semibold small" style="color: #2B2D42;">Notifikasi</span>
                                                <span
                                                    id="dropdown-unread-pill"
                                                    class="badge rounded-pill bg-primary {{ $sidebarUnreadCount > 0 ? '' : 'd-none' }}"
                                                    style="font-size: 10px;"
                                                >
                                                    <span id="dropdown-unread-count-text">{{ $sidebarUnreadCount }}</span> baru
                                                </span>
                                            </div>
                                            <form id="mark-all-read-form" method="POST" action="{{ route('notifications.read-all') }}">
                                                @csrf
                                                <button
                                                    type="submit"
                                                    class="btn btn-link p-0 text-decoration-none small text-muted"
                                                    style="font-size: 11px;"
                                                    onmouseover="this.style.color='#4361EE';"
                                                    onmouseout="this.style.color='#8D99AE';"
                                                >
                                                    Tandai semua dibaca
                                                </button>
                                            </form>
                                        </div>

                                        <!-- Quick Notification Preferences (Browser & Sound) -->
                                        <div class="px-3 py-1.5 bg-light-subtle border-bottom d-flex align-items-center justify-content-between" style="border-color: #F1F5F9 !important; font-size: 11px;">
                                            <div class="d-flex align-items-center gap-1">
                                                <button type="button" id="tm-toggle-sound-btn" class="btn btn-link p-0 text-decoration-none text-muted d-inline-flex align-items-center gap-1" style="font-size: 11px;" title="Aktifkan/Nonaktifkan Suara Pengingat">
                                                    <span id="tm-sound-icon">🔔</span>
                                                    <span id="tm-sound-status-text">Suara: Off</span>
                                                </button>
                                            </div>
                                            <div id="tm-browser-notif-wrapper">
                                                <button type="button" id="tm-request-browser-notif-btn" class="btn btn-link p-0 text-decoration-none text-primary fw-medium" style="font-size: 11px;">
                                                    Aktifkan Notif Browser
                                                </button>
                                            </div>
                                        </div>

                                        <!-- Dropdown Notifications List -->
                                        <div id="header-notifications-list" class="overflow-y-auto" style="max-height: 320px;">
                                            @forelse ($headerNotifications ?? [] as $item)
                                                @php
                                                    $isItemUnread = $item->unread();
                                                    $itemData = $item->data;
                                                    $itemType = $itemData['type'] ?? 'info';
                                                    $itemTaskId = $itemData['task_id'] ?? null;
                                                    $itemUrl = $itemData['url'] ?? ($itemTaskId ? route('tasks.show', $itemTaskId) : '#');
                                                @endphp
                                                <div class="notification-item p-2.5 border-bottom d-flex align-items-start gap-2.5 {{ $isItemUnread ? 'bg-primary-subtle bg-opacity-25' : '' }}" style="border-color: #F1F5F9 !important;">
                                                    <div class="mt-1 flex-shrink-0">
                                                        @if ($itemType === 'overdue' || $itemType === 'reminder_10m')
                                                            <span class="d-inline-block rounded-circle" style="width: 8px; height: 8px; background-color: #EF233C;"></span>
                                                        @elseif ($itemType === 'reminder_1h')
                                                            <span class="d-inline-block rounded-circle" style="width: 8px; height: 8px; background-color: #4F46E5;"></span>
                                                        @elseif ($itemType === 'due_today')
                                                            <span class="d-inline-block rounded-circle" style="width: 8px; height: 8px; background-color: #F59E0B;"></span>
                                                        @else
                                                            <span class="d-inline-block rounded-circle" style="width: 8px; height: 8px; background-color: #4361EE;"></span>
                                                        @endif
                                                    </div>
                                                    <div class="flex-grow-1 min-w-0">
                                                        <a href="{{ $itemUrl }}" class="text-decoration-none d-block">
                                                            <div class="fw-semibold text-truncate small" style="color: #2B2D42;">
                                                                {{ $itemData['title'] ?? 'Notifikasi Task' }}
                                                            </div>
                                                            <div class="text-secondary small text-truncate" style="font-size: 12px;">
                                                                {{ $itemData['message'] ?? '' }}
                                                            </div>
                                                        </a>
                                                        <div class="d-flex align-items-center justify-content-between mt-1" style="font-size: 11px; color: #8D99AE;">
                                                            <span>{{ $item->created_at->diffForHumans() }}</span>
                                                            @if ($isItemUnread)
                                                                <form method="POST" action="{{ route('notifications.read', $item->id) }}" class="mark-single-read-form">
                                                                    @csrf
                                                                    <button type="submit" class="btn btn-link p-0 text-decoration-none text-muted" style="font-size: 11px;">
                                                                        Tandai dibaca
                                                                    </button>
                                                                </form>
                                                            @endif
                                                        </div>
                                                    </div>
                                                </div>
                                            @empty
                                                <div id="header-notifications-empty" class="p-4 text-center text-muted small">
                                                    <x-iconly name="notification" size="24" class="mb-1 opacity-50 d-block mx-auto" />
                                                    <span>Belum ada notifikasi</span>
                                                </div>
                                            @endforelse
                                        </div>

                                        <!-- Dropdown Footer -->
                                        <div class="p-2 border-top text-center bg-white" style="border-color: #DFE5EC !important;">
                                            <a href="{{ route('notifications.index') }}" class="small text-decoration-none fw-semibold" style="color: #4361EE;">
                                                Buka Notification Center &rarr;
                                            </a>
                                        </div>
                                    </div>
                                </div>

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
                                                    <span class="badge rounded-pill profile-unread-badge {{ $sidebarUnreadCount > 0 ? '' : 'd-none' }}" style="background-color: #4361EE; font-size: 10px;">
                                                        <span class="profile-unread-count-text">{{ $sidebarUnreadCount }}</span>
                                                    </span>
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

        <!-- Realtime Notification Toast Container -->
        <div id="realtime-toast-container" class="position-fixed top-0 end-0 p-3" style="z-index: 1090; max-width: 360px; width: 100%; pointer-events: none;"></div>

        <style>
            @media (min-width: 768px) {
                .desktop-main-wrapper {
                    margin-left: 250px !important;
                }
            }

            @keyframes slideInRight {
                from {
                    opacity: 0;
                    transform: translateX(100%);
                }
                to {
                    opacity: 1;
                    transform: translateX(0);
                }
            }
        </style>
    </body>
</html>
