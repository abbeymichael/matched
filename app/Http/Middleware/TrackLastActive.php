<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class TrackLastActive
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = Auth::user();

        if ($user) {
            $user->forceFill(['last_active_at' => now()])->save();
        }

        return $next($request);
    }
}
