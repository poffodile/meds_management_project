<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use Carbon\Carbon;

class ClearLogs extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    // protected $signature = 'app:clear-logs';
    protected $signature = 'log:clear';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Execute the console command.
     */
    public function handle()
    {
 $logFile = storage_path('logs/laravel.log');

        if (!file_exists($logFile)) {
            $this->error('Log file not found.');
            return;
        }

        $cutoff = Carbon::now()->subDays(30);
        $newContent = '';

        $handle = fopen($logFile, 'r');

        while (($line = fgets($handle)) !== false) {
            // Match timestamp inside [ ... ]
            if (preg_match('/\[(.*?)\]/', $line, $matches)) {
                $timestamp = Carbon::parse($matches[1]);

                // Keep only log entries from last 30 days
                if ($timestamp >= $cutoff) {
                    $newContent .= $line;
                }
            }
        }

        fclose($handle);

        // Replace file with filtered logs
        file_put_contents($logFile, $newContent);

        $this->info("Old log entries deleted successfully!");
        $this->info("Remaining file size: " . strlen($newContent) . " bytes");
    }
}
