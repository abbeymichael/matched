# Synchrony

Synchrony is an intentional, preference-first matchmaking product built for Ghanaian adults. This repository is scaffolded for Laravel 12, Livewire 4, Tailwind CSS 4, and custom phone + OTP authentication.

## Local setup

PHP 8.2+ and Composer are required for the Laravel runtime.

```bash
composer install
cp .env.example .env
php artisan key:generate
mkdir -p database && touch database/database.sqlite
php artisan migrate
npm install
npm run dev
```

The current first slice includes the visual dashboard direction and a phone-entry auth screen. Authentication is intentionally application-owned (no Breeze, Jetstream, Fortify, or social auth package); OTP delivery will be provider-backed through an internal service interface.
