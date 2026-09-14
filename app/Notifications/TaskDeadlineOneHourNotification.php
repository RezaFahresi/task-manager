<?php

namespace App\Notifications;

use App\Models\Task;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\BroadcastMessage;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class TaskDeadlineOneHourNotification extends Notification implements ShouldBroadcast, ShouldQueue
{
    use Queueable;

    public function __construct(public Task $task) {}

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['database', 'mail', 'broadcast'];
    }

    /**
     * Determine which connections should be used for each channel.
     *
     * @return array<string, string>
     */
    public function viaConnections(): array
    {
        return [
            'database' => 'sync',
            'broadcast' => 'sync',
        ];
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail(object $notifiable): MailMessage
    {
        $dueDate = $this->task->due_date ? $this->task->due_date->format('d M Y, H:i') : '1 Jam Lagi';
        $url = route('tasks.show', $this->task);

        return (new MailMessage)
            ->subject("[Pengingat] 1 Jam Menuju Deadline: {$this->task->title}")
            ->view('emails.task-reminder', [
                'user' => $notifiable,
                'task' => $this->task,
                'type' => 'reminder_1h',
                'heading' => 'Pengingat Deadline: 1 Jam Tersisa',
                'messageContent' => 'Task "'.$this->task->title.'" akan segera jatuh tempo dalam 1 jam ke depan. Harap selesaikan pekerjaan Anda tepat waktu.',
                'dueDate' => $dueDate,
                'url' => $url,
            ]);
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            'task_id' => $this->task->id,
            'task_title' => $this->task->title,
            'title' => 'Pengingat: 1 Jam Menuju Deadline',
            'message' => 'Task "'.$this->task->title.'" jatuh tempo dalam 1 jam.',
            'type' => 'reminder_1h',
            'notification_type' => 'reminder_1h',
            'due_date' => $this->getDeadlineString(),
            'url' => route('tasks.show', $this->task),
            'urgency' => 'normal',
        ];
    }

    /**
     * Get the broadcastable representation of the notification.
     */
    public function toBroadcast(object $notifiable): BroadcastMessage
    {
        return (new BroadcastMessage([
            'id' => $this->id,
            'task_id' => $this->task->id,
            'task_title' => $this->task->title,
            'title' => 'Pengingat: 1 Jam Menuju Deadline',
            'message' => 'Task "'.$this->task->title.'" jatuh tempo dalam 1 jam.',
            'type' => 'reminder_1h',
            'notification_type' => 'reminder_1h',
            'due_date' => $this->getDeadlineString(),
            'url' => route('tasks.show', $this->task),
            'urgency' => 'normal',
            'created_at' => now()->diffForHumans(),
        ]))->onConnection('sync');
    }

    protected function getDeadlineString(): ?string
    {
        if (! $this->task->due_date) {
            return null;
        }

        return $this->task->due_date->format('Y-m-d H:i:s');
    }
}
