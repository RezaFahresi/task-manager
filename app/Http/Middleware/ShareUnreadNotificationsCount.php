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
            View::share('unreadNotificationCount', Auth::user()->unreadNotifications()->count());
        } else {
            View::share('unreadNotificationCount', 0);
        }

        return $next($request);
    }
}
