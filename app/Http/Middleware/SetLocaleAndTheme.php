<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\View;
use Symfony\Component\HttpFoundation\Response;

class SetLocaleAndTheme
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $userId = $request->user()?->id;
        $sessionKey = $userId ? "user_{$userId}_settings" : 'guest_settings';

        // 1. Resolve Settings (Session -> Cache -> Default)
        $settings = Session::get($sessionKey, function() use ($userId, $sessionKey) {
            if ($userId) {
                return Cache::get($sessionKey, ['locale' => config('app.locale'), 'theme' => 'dark']);
            }
            return ['locale' => config('app.locale'), 'theme' => 'dark']; // Default guest settings
        });

        // 2. Set Locale
        App::setLocale($settings['locale']);

        // 3. Share Theme with Views (for Layouts)
        View::share('appTheme', $settings['theme']);
        View::share('appLocale', $settings['locale']);

        return $next($request);
    }
}
