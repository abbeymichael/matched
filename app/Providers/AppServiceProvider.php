<?php

namespace App\Providers;

use App\Contracts\SmsProviderInterface;
use App\Services\LogSmsProvider;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->bind(SmsProviderInterface::class, LogSmsProvider::class);
    }

    public function boot(): void {}
}
