<?php

namespace App\Console\Commands;

use App\Services\TaskNotificationService;
use Illuminate\Console\Command;

class CheckSmartRemindersCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'tasks:smart-reminders';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Check smart deadline reminders (1 hour, 10 min, overdue) and dispatch in-app, realtime, and email notifications';

    /**
     * Execute the console command.
     */
    public function handle(TaskNotificationService $notificationService): int
    {
        $this->info('Checking smart task deadline reminders...');

        $sentCount = $notificationService->checkAndNotifyAll();

        $this->info("Successfully dispatched {$sentCount} smart reminder notifications.");

        return Command::SUCCESS;
    }
}
