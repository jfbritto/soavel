<?php

namespace App\Http\Middleware;

use App\Models\Setting;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;

class EnsureOnboardingComplete
{
    private const REQUIRED_KEYS = ['nome_sistema', 'whatsapp_number'];

    public function handle(Request $request, Closure $next)
    {
        if (! Schema::hasTable('settings')) {
            return $next($request);
        }

        // Já está na rota de onboarding? Deixa passar
        if ($request->routeIs('admin.onboarding.*')) {
            return $next($request);
        }

        foreach (self::REQUIRED_KEYS as $key) {
            $value = Setting::get($key);
            if (empty($value)) {
                return redirect()->route('admin.onboarding.index');
            }
        }

        return $next($request);
    }
}
