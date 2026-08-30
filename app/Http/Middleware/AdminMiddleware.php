<?php

namespace App\Http\Middleware;

use App\Enums\UserRole;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class AdminMiddleware
{
    public function handle(Request $request, Closure $next): Response
    {
        if (! $request->user() || ! $request->user()->is_active || $request->user()->role !== UserRole::Admin) {
            return redirect()->route('admin.login')
                ->with('error', 'You do not have permission to access the admin panel.');
        }

        return $next($request);
    }
}
