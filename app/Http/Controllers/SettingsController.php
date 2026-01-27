<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Session;

class SettingsController extends Controller
{
    public function setLocale(Request $request, $locale)
    {
        if (!in_array($locale, ['en', 'tr'])) {
            abort(400);
        }

        $this->updateSettings('locale', $locale);
        return back();
    }

    public function setTheme(Request $request, $theme)
    {
        if (!in_array($theme, ['light', 'dark', 'system'])) {
            $theme = 'light';
        }

        $this->updateSettings('theme', $theme);

        if ($request->wantsJson()) {
            return response()->json(['status' => 'success', 'theme' => $theme]);
        }
        
        return back();
    }

    private function updateSettings($key, $value)
    {
        $userId = auth()->id();
        $sessionKey = $userId ? "user_{$userId}_settings" : 'guest_settings';

        $settings = Session::get($sessionKey, ['locale' => config('app.locale'), 'theme' => 'dark']);
        $settings[$key] = $value;

        Session::put($sessionKey, $settings);

        if ($userId) {
            Cache::put($sessionKey, $settings, now()->addDays(30));
        }
    }
}
