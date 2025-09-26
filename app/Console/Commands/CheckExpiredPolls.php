<?php

namespace App\Console\Commands;

use App\Models\Poll;
use Illuminate\Console\Command;

class CheckExpiredPolls extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'polls:check-expired';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Check for expired polls and end them automatically';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Checking for expired polls...');

        $expiredCount = Poll::checkExpiredPolls();

        if ($expiredCount > 0) {
            $this->info("Ended {$expiredCount} expired poll(s).");
        } else {
            $this->info('No expired polls found.');
        }

        return 0;
    }
}

