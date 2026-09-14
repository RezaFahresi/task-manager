<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\View;
use Symfony\Component\HttpFoundation\Response;

class ShareUnreadNotificationsCount
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (Auth::check()) {
            $user = Auth::user();
            View::share('unreadNotificationCount', $user->unreadNotifications()->count());
            View::share('headerNotifications', $user->unreadNotifications()->latest()->take(5)->get());
        } else {
            View::share('unreadNotificationCount', 0);
            View::share('headerNotifications', collect());
        }

        return $next($request);
    }
}
