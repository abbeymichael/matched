<?php

use App\Http\Middleware\EnsureNotBanned;
use App\Http\Middleware\EnsurePhoneVerified;
use App\Http\Middleware\EnsureProfileLocked;
use App\Http\Middleware\EnsureVerifiedIdentity;
use App\Http\Middleware\TrackLastActive;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->alias([
            'verified' => EnsurePhoneVerified::class,
            'locked' => EnsureProfileLocked::class,
            'not.banned' => EnsureNotBanned::class,
            'verified.identity' => EnsureVerifiedIdentity::class,
            'track.active' => TrackLastActive::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })->create();
