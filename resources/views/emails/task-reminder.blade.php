<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $heading ?? 'Pengingat Task' }}</title>
    <style>
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif;
            background-color: #f4f5f7;
            color: #1e293b;
            margin: 0;
            padding: 0;
            -webkit-font-smoothing: antialiased;
        }
        .wrapper {
            width: 100%;
            background-color: #f4f5f7;
            padding: 40px 16px;
        }
        .container {
            max-width: 580px;
            margin: 0 auto;
            background-color: #ffffff;
            border-radius: 8px;
            overflow: hidden;
            border: 1px solid #e2e8f0;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.05);
        }
        .header {
            background-color: #0f172a;
            padding: 24px 32px;
            color: #ffffff;
        }
        .header-title {
            font-size: 18px;
            font-weight: 700;
            letter-spacing: -0.01em;
            margin: 0;
            color: #ffffff;
        }
        .content {
            padding: 32px;
        }
        .badge {
            display: inline-block;
            padding: 4px 10px;
            font-size: 12px;
            font-weight: 600;
            border-radius: 4px;
            text-transform: uppercase;
            letter-spacing: 0.05em;
            margin-bottom: 16px;
        }
        .badge-due-today {
            background-color: #fef3c7;
            color: #92400e;
        }
        .badge-overdue {
            background-color: #fee2e2;
            color: #991b1b;
        }
        .badge-due-soon {
            background-color: #dbeafe;
            color: #1e40af;
        }
        .badge-reminder-10m {
            background-color: #ffedd5;
            color: #c2410c;
        }
        .badge-reminder-1h {
            background-color: #e0e7ff;
            color: #4338ca;
        }
        .task-card {
            background-color: #f8fafc;
            border: 1px solid #e2e8f0;
            border-radius: 6px;
            padding: 20px;
            margin: 20px 0;
        }
        .task-title {
            font-size: 18px;
            font-weight: 700;
            color: #0f172a;
            margin: 0 0 12px 0;
        }
        .task-meta-table {
            width: 100%;
            border-collapse: collapse;
        }
        .task-meta-table td {
            padding: 6px 0;
            font-size: 14px;
            vertical-align: top;
        }
        .meta-label {
            color: #64748b;
            width: 130px;
            font-weight: 500;
        }
        .meta-value {
            color: #1e293b;
            font-weight: 600;
        }
        .btn-wrapper {
            margin: 28px 0 20px 0;
            text-align: center;
        }
        .btn-primary {
            display: inline-block;
            background-color: #2563eb;
            color: #ffffff !important;
            padding: 12px 28px;
            font-size: 14px;
            font-weight: 600;
            text-decoration: none;
            border-radius: 6px;
        }
        .divider {
            border-top: 1px solid #e2e8f0;
            margin: 24px 0;
        }
        .subtext {
            font-size: 12px;
            color: #64748b;
            line-height: 1.5;
            word-break: break-all;
        }
        .footer {
            background-color: #f8fafc;
            border-top: 1px solid #e2e8f0;
            padding: 20px 32px;
            text-align: center;
            font-size: 12px;
            color: #94a3b8;
        }
    </style>
</head>
<body>
    <div class="wrapper">
        <div class="container">
            <div class="header">
                <h1 class="header-title">Task Manager</h1>
            </div>
            <div class="content">
                @if(($type ?? '') === 'overdue')
                    <span class="badge badge-overdue">Terlambat (Overdue)</span>
                @elseif(($type ?? '') === 'reminder_10m')
                    <span class="badge badge-reminder-10m">Mendesak (10 Menit Menuju Deadline)</span>
                @elseif(($type ?? '') === 'reminder_1h')
                    <span class="badge badge-reminder-1h">1 Jam Menuju Deadline</span>
                @elseif(($type ?? '') === 'due_today')
                    <span class="badge badge-due-today">Jatuh Tempo Hari Ini</span>
                @else
                    <span class="badge badge-due-soon">Mendatang (Due Soon)</span>
                @endif

                <p style="margin: 0 0 8px 0; font-size: 15px; color: #334155;">Halo <strong>{{ $user->name }}</strong>,</p>
                <p style="margin: 0 0 16px 0; font-size: 14px; color: #475569; line-height: 1.5;">{{ $messageContent }}</p>

                <div class="task-card">
                    <h2 class="task-title">{{ $task->title }}</h2>
                    <table class="task-meta-table">
                        <tr>
                            <td class="meta-label">Tenggat Waktu:</td>
                            <td class="meta-value">{{ $dueDate }}</td>
                        </tr>
                        <tr>
                            <td class="meta-label">Prioritas:</td>
                            <td class="meta-value">{{ ucfirst($task->priority ?? 'medium') }}</td>
                        </tr>
                        @if($task->category)
                        <tr>
                            <td class="meta-label">Kategori:</td>
                            <td class="meta-value">{{ $task->category->name }}</td>
                        </tr>
                        @endif
                        @if($task->description)
                        <tr>
                            <td class="meta-label">Deskripsi:</td>
                            <td class="meta-value" style="font-weight: 400; color: #475569;">{{ $task->description }}</td>
                        </tr>
                        @endif
                    </table>
                </div>

                <div class="btn-wrapper">
                    <a href="{{ $url }}" class="btn-primary" target="_blank">Buka Detail Task &rarr;</a>
                </div>

                <div class="divider"></div>

                <p class="subtext">
                    Jika tombol di atas tidak berfungsi, salin tautan berikut ke browser Anda:<br>
                    <a href="{{ $url }}" style="color: #2563eb; text-decoration: underline;">{{ $url }}</a>
                </p>
            </div>
            <div class="footer">
                &copy; {{ date('Y') }} Task Manager. Pengingat otomatis untuk produktivitas Anda.
            </div>
        </div>
    </div>
</body>
</html>
