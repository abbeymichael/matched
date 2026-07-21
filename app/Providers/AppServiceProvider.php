<?php

namespace App\Providers;

use App\Contracts\SmsProviderInterface;
use App\Models\User;
use App\Services\LogSmsProvider;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->bind(SmsProviderInterface::class, LogSmsProvider::class);
    }

    public function boot(): void
    {
        // Gates all /admin/* Livewire routes and /api/v1/admin/* endpoints (§8.3).
        Gate::define('admin', fn (User $user) => (bool) $user->is_admin);
    }
}
