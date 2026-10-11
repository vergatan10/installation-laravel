<?php

namespace Vergatan10\Installer\Http\Middleware;

use Vergatan10\Installer\Support\Installer;
use Closure;
use Illuminate\Http\Request;

class EnsureNotInstalled
{
    public function handle(Request $request, Closure $next)
    {
        if (Installer::isInstalled()) {
            if ($request->expectsJson()) {
                return response()->json(['message' => 'Application is already installed.'], 403);
            }

            return redirect(config('installer.redirect_after_install', '/'));
        }

        return $next($request);
    }
}
