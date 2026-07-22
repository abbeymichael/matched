<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class EnsureProfileLocked
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = Auth::user();

        if (! $user || ! $user->profile_locked) {
            return redirect()->route('onboarding.profile');
        }

        return $next($request);
    }
}
