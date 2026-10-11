<?php

namespace Vergatan10\Installer\Http\Middleware;

use Vergatan10\Installer\Support\Installer;
use Closure;
use Illuminate\Http\Request;

class RedirectIfNotInstalled
{
    public function handle(Request $request, Closure $next)
    {
        if (Installer::isInstalled()
            || $request->routeIs('installer.*')
            || $request->is(...config('installer.except', []))) {
            return $next($request);
        }

        if ($request->expectsJson()) {
            return response()->json(['message' => 'Application is not installed yet.'], 503);
        }

        return redirect()->route('installer.welcome');
    }
}
