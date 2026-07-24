<?php

namespace App\Console\Commands;

use App\Models\OtpCode;
use Illuminate\Console\Command;

/**
 * php artisan otp:prune — deletes expired OTP rows (§9, run via scheduler daily).
 */
final class PruneExpiredOtps extends Command
{
    protected $signature = 'otp:prune';

    protected $description = 'Delete expired OTP codes.';

    public function handle(): int
    {
        $deleted = OtpCode::where('expires_at', '<', now())->delete();

        $this->info("Pruned {$deleted} expired OTP codes.");

        return self::SUCCESS;
    }
}
