<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Cache;

class Task extends Model
{
    protected $fillable = [
        'user_id',
        'title',
        'description',
        'status',
        'priority',
        'due_date',
        'category_id',
    ];

    /**
     * The model's default values for attributes.
     *
     * @var array<string, mixed>
     */
    protected $attributes = [
        'priority' => 'medium',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'due_date' => 'datetime',
        ];
    }

    public function setDueDateAttribute(mixed $value): void
    {
        if (! $value) {
            $this->attributes['due_date'] = null;

            return;
        }

        if (is_string($value) && preg_match('/^\d{4}-\d{2}-\d{2}$/', trim($value))) {
            $this->attributes['due_date'] = trim($value);

            return;
        }

        $parsed = Carbon::parse($value);
        $this->attributes['due_date'] = $parsed->format('Y-m-d H:i:s');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    /**
     * The "booted" method of the model.
     */
    protected static function booted(): void
    {
        static::updating(function (Task $task): void {
            // When task status is marked completed, clean up active overdue and reminder notifications
            // and clear dismissal cache so if it is ever reopened, it can be notified freshly.
            if ($task->isDirty('status') && $task->status === 'completed') {
                if ($task->user_id && $task->user) {
                    $task->user->notifications()
                        ->get()
                        ->filter(function ($notification) use ($task): bool {
                            $data = $notification->data;

                            return is_array($data)
                                && isset($data['task_id'], $data['type'])
                                && (int) $data['task_id'] === (int) $task->id
                                && in_array($data['type'], ['overdue', 'reminder_1h', 'reminder_10m']);
                        })
                        ->each(function ($notification): void {
                            $notification->delete();
                        });

                    $dueDateStr = $task->due_date ? $task->due_date->format('Y-m-d H:i:s') : 'none';
                    $dueDateOnly = $task->due_date ? $task->due_date->toDateString() : 'none';

                    Cache::forget("dismissed_notification:{$task->user_id}:{$task->id}:overdue:{$dueDateStr}");
                    Cache::forget("dismissed_notification:{$task->user_id}:{$task->id}:overdue:{$dueDateOnly}");
                    Cache::forget("dismissed_notification:{$task->user_id}:{$task->id}:reminder_1h:{$dueDateStr}");
                    Cache::forget("dismissed_notification:{$task->user_id}:{$task->id}:reminder_1h:{$dueDateOnly}");
                    Cache::forget("dismissed_notification:{$task->user_id}:{$task->id}:reminder_10m:{$dueDateStr}");
                    Cache::forget("dismissed_notification:{$task->user_id}:{$task->id}:reminder_10m:{$dueDateOnly}");
                }
            }

            // When due_date is rescheduled, clean up old overdue and reminder notifications for the previous deadline
            if ($task->isDirty('due_date')) {
                if ($task->user_id && $task->user) {
                    $oldDueDate = $task->getOriginal('due_date');
                    $oldDueDateCarbon = $oldDueDate ? Carbon::parse($oldDueDate) : null;
                    $oldDueDateStr = $oldDueDateCarbon ? $oldDueDateCarbon->format('Y-m-d H:i:s') : 'none';
                    $oldDueDateOnly = $oldDueDateCarbon ? $oldDueDateCarbon->toDateString() : 'none';

                    $task->user->notifications()
                        ->get()
                        ->filter(function ($notification) use ($task, $oldDueDateStr, $oldDueDateOnly): bool {
                            $data = $notification->data;

                            if (! is_array($data) || ! isset($data['task_id'], $data['type'])) {
                                return false;
                            }

                            if ((int) $data['task_id'] !== (int) $task->id) {
                                return false;
                            }

                            if (in_array($data['type'], ['overdue', 'reminder_1h', 'reminder_10m'])) {
                                $notifDue = $data['due_date'] ?? null;

                                return $notifDue === $oldDueDateStr || $notifDue === $oldDueDateOnly || $notifDue === null;
                            }

                            return false;
                        })
                        ->each(function ($notification): void {
                            $notification->delete();
                        });

                    Cache::forget("dismissed_notification:{$task->user_id}:{$task->id}:overdue:{$oldDueDateStr}");
                    Cache::forget("dismissed_notification:{$task->user_id}:{$task->id}:overdue:{$oldDueDateOnly}");
                    Cache::forget("dismissed_notification:{$task->user_id}:{$task->id}:reminder_1h:{$oldDueDateStr}");
                    Cache::forget("dismissed_notification:{$task->user_id}:{$task->id}:reminder_1h:{$oldDueDateOnly}");
                    Cache::forget("dismissed_notification:{$task->user_id}:{$task->id}:reminder_10m:{$oldDueDateStr}");
                    Cache::forget("dismissed_notification:{$task->user_id}:{$task->id}:reminder_10m:{$oldDueDateOnly}");
                }
            }
        });

        static::deleting(function (Task $task): void {
            if ($task->user_id && $task->user) {
                $task->user->notifications()
                    ->get()
                    ->filter(function ($notification) use ($task): bool {
                        $data = $notification->data;

                        return is_array($data)
                            && isset($data['task_id'])
                            && (int) $data['task_id'] === (int) $task->id;
                    })
                    ->each(function ($notification): void {
                        $notification->delete();
                    });
            }
        });
    }
}
