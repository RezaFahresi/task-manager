<?php

namespace App\Notifications;

use App\Models\Task;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\BroadcastMessage;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class TaskOverdueNotification extends Notification implements ShouldBroadcast, ShouldQueue
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
        $dueDate = $this->task->due_date?->format('d M Y') ?? 'Terlambat';
        $url = route('tasks.show', $this->task);

        return (new MailMessage)
            ->subject("[Perhatian] Task Terlambat (Overdue): {$this->task->title}")
            ->view('emails.task-reminder', [
                'user' => $notifiable,
                'task' => $this->task,
                'type' => 'overdue',
                'heading' => 'Task Terlambat (Overdue)',
                'messageContent' => 'Task "'.$this->task->title.'" telah melewati tenggat waktu yang ditentukan. Harap segera meninjau dan memperbarui task Anda.',
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
            'title' => 'Task Menjadi Overdue',
            'message' => 'Task "'.$this->task->title.'" telah melewati batas waktu (terlambat).',
            'type' => 'overdue',
            'due_date' => $this->task->due_date?->toDateString(),
            'url' => route('tasks.show', $this->task),
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
            'title' => 'Task Menjadi Overdue',
            'message' => 'Task "'.$this->task->title.'" telah melewati batas waktu (terlambat).',
            'type' => 'overdue',
            'notification_type' => 'overdue',
            'due_date' => $this->task->due_date?->toDateString(),
            'url' => route('tasks.show', $this->task),
            'created_at' => now()->diffForHumans(),
        ]))->onConnection('sync');
    }
}
