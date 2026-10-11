<?php

namespace Vergatan10\Installer\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class SetInstallerLocale
{
    public function handle(Request $request, Closure $next)
    {
        $locales = array_keys(config('installer.locales', []));
        $locale = $request->session()->get('installer.locale', config('installer.locale'));

        if ($locale && (! $locales || in_array($locale, $locales, true))) {
            app()->setLocale($locale);
        }

        return $next($request);
    }
}
