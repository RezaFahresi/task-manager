<div class="d-flex flex-column justify-content-between h-100 overflow-y-auto">
    <!-- Navigation Links -->
    <nav class="nav flex-column p-3 gap-1">
        <!-- Dashboard -->
        <a
            href="{{ route('dashboard') }}"
            class="nav-link d-flex align-items-center gap-2.5 px-3 py-2 rounded-2 text-decoration-none transition-all {{ request()->routeIs('dashboard') ? 'bg-primary-subtle text-primary fw-semibold' : 'text-secondary hover-dark' }}"
            style="{{ request()->routeIs('dashboard') ? 'background-color: #EEF2FF; color: #4361EE;' : 'color: #8D99AE;' }}"
        >
            <x-iconly name="category" size="18" class="{{ request()->routeIs('dashboard') ? 'text-primary' : '' }}" />
            <span class="small">Dashboard</span>
        </a>

        <!-- Tasks -->
        <a
            href="{{ route('tasks.index') }}"
            class="nav-link d-flex align-items-center gap-2.5 px-3 py-2 rounded-2 text-decoration-none transition-all {{ request()->routeIs('tasks.*') ? 'bg-primary-subtle text-primary fw-semibold' : 'text-secondary hover-dark' }}"
            style="{{ request()->routeIs('tasks.*') ? 'background-color: #EEF2FF; color: #4361EE;' : 'color: #8D99AE;' }}"
        >
            <x-iconly name="tick-square" size="18" class="{{ request()->routeIs('tasks.*') ? 'text-primary' : '' }}" />
            <span class="small">Tasks</span>
        </a>

        <!-- Categories -->
        <a
            href="{{ route('categories.index') }}"
            class="nav-link d-flex align-items-center gap-2.5 px-3 py-2 rounded-2 text-decoration-none transition-all {{ request()->routeIs('categories.*') ? 'bg-primary-subtle text-primary fw-semibold' : 'text-secondary hover-dark' }}"
            style="{{ request()->routeIs('categories.*') ? 'background-color: #EEF2FF; color: #4361EE;' : 'color: #8D99AE;' }}"
        >
            <x-iconly name="folder" size="18" class="{{ request()->routeIs('categories.*') ? 'text-primary' : '' }}" />
            <span class="small">Categories</span>
        </a>

        <!-- Notifications -->
        <a
            href="{{ route('notifications.index') }}"
            class="nav-link d-flex align-items-center justify-content-between px-3 py-2 rounded-2 text-decoration-none transition-all {{ request()->routeIs('notifications.*') ? 'bg-primary-subtle text-primary fw-semibold' : 'text-secondary hover-dark' }}"
            style="{{ request()->routeIs('notifications.*') ? 'background-color: #EEF2FF; color: #4361EE;' : 'color: #8D99AE;' }}"
        >
            <div class="d-flex align-items-center gap-2.5">
                <x-iconly name="notification" size="18" class="{{ request()->routeIs('notifications.*') ? 'text-primary' : '' }}" />
                <span class="small">Notifications</span>
            </div>
            @php
                $sidebarUnreadCount = $unreadNotificationCount ?? 0;
            @endphp
            <span
                class="badge rounded-pill sidebar-unread-badge {{ $sidebarUnreadCount > 0 ? '' : 'd-none' }}"
                style="background-color: #4361EE; color: #FFFFFF; font-size: 10px; font-weight: 600; padding: 3px 7px;"
            >
                <span class="sidebar-unread-count-text">{{ $sidebarUnreadCount }}</span>
            </span>
        </a>

        <div class="my-2 border-top" style="border-color: #DFE5EC !important;"></div>

        <!-- Profile -->
        <a
            href="{{ route('profile.edit') }}"
            class="nav-link d-flex align-items-center gap-2.5 px-3 py-2 rounded-2 text-decoration-none transition-all {{ request()->routeIs('profile.*') ? 'bg-primary-subtle text-primary fw-semibold' : 'text-secondary hover-dark' }}"
            style="{{ request()->routeIs('profile.*') ? 'background-color: #EEF2FF; color: #4361EE;' : 'color: #8D99AE;' }}"
        >
            <x-iconly name="profile" size="18" class="{{ request()->routeIs('profile.*') ? 'text-primary' : '' }}" />
            <span class="small">Profile</span>
        </a>
    </nav>

    <!-- Bottom Section: User Info & Logout -->
    <div class="p-3 border-top mt-auto bg-white" style="border-color: #DFE5EC !important;">
        @auth
            <div class="px-2 py-1 mb-2">
                <p class="mb-0 fw-semibold text-truncate small" style="color: #2B2D42;">
                    {{ Auth::user()->name }}
                </p>
                <p class="mb-0 text-truncate" style="color: #8D99AE; font-size: 11px;">
                    {{ Auth::user()->email }}
                </p>
            </div>
            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button
                    type="submit"
                    class="btn btn-sm w-100 d-flex align-items-center justify-content-between px-2.5 py-1.5 rounded-2 border-0 text-decoration-none"
                    style="color: #8D99AE; background: transparent; font-size: 12px; font-weight: 500;"
                    onmouseover="this.style.color='#EF233C'; this.style.backgroundColor='#FEF2F2';"
                    onmouseout="this.style.color='#8D99AE'; this.style.backgroundColor='transparent';"
                >
                    <span>Keluar</span>
                    <x-iconly name="logout" size="14" />
                </button>
            </form>
        @endauth
    </div>
</div>
