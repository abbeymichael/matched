<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class EnsureNotBanned
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = Auth::user();

        if (! $user) {
            return $next($request);
        }

        if ($user->status === 'banned') {
            return redirect()->route('banned');
        }

        if (in_array($user->status, ['suspended', 'under_review'], true)) {
            return redirect()->route('under-review');
        }

        return $next($request);
    }
}
