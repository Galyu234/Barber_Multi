<?php

namespace App\Console\Commands;

use App\Services\QueueService;
use Illuminate\Console\Command;

class AutoTimeoutQueues extends Command
{
    protected $signature   = 'queue:auto-timeout';
    protected $description = 'Auto-timeout antrian yang melebihi batas waktu';

    public function handle(QueueService $queueService): int
    {
        $count = $queueService->autoTimeout();
        $this->info("✔ {$count} antrian di-timeout secara otomatis.");
        return Command::SUCCESS;
    }
}
