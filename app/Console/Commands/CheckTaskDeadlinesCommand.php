<?php

namespace App\Console\Commands;

use App\Services\TaskNotificationService;
use Illuminate\Console\Command;

class CheckTaskDeadlinesCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'tasks:check-deadlines';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Check task deadlines and dispatch in-app notifications for due today, overdue, and upcoming tasks';

    /**
     * Execute the console command.
     */
    public function handle(TaskNotificationService $notificationService): int
    {
        $this->info('Checking task deadlines...');

        $sentCount = $notificationService->checkAndNotifyAll();

        $this->info("Successfully dispatched {$sentCount} notifications.");

        return Command::SUCCESS;
    }
}
