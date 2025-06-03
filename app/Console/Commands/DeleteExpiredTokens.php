<?php

namespace App\Console\Commands;

use App\Helpers\LoggerHelper;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class DeleteExpiredTokens extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'token:delete-expired-tokens';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Delete expired access tokens from database.';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        LoggerHelper::info('CRON STARTED: tokens:deleted-expired-tokens.');

        try {
            $expiredCount = DB::table('personal_access_tokens')
                ->where('expires_at', '<', now())
                ->delete();

            $message = "CRON SUCCESS: Deleted $expiredCount expired access tokens.";
            $this->info($message);
            LoggerHelper::info($message);

        } catch (\Throwable $th) {
            $this->error('CRON ERROR: Failed to delete expired tokens.');
            LoggerHelper::error('CRON ERROR: Failed to delete expired tokens.', [
                'error' => $th->getMessage()
            ]);
        }

        LoggerHelper::info('CRON ENDED: token:delete-expired-tokens.');
    }
}
