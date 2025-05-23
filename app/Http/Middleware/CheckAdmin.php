<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class CheckAdmin
{
    public function handle(Request $request, Closure $next)
    {
        if (!auth()->check() || !auth()->user()->is_admin) {
            return response()->json(['message' => 'Forbidden. Admins only.'], 403);
        }

        return $next($request);
    }
}
