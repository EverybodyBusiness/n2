<?php

namespace App\Listeners\System;

use App\Models\User;
use App\Notifications\System\CriticalErrorNotification;
use Illuminate\Support\Facades\Notification;
use Laravel\Telescope\Events\EntryRecorded;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

class TelescopeErrorListener implements ShouldQueue
{
    use InteractsWithQueue;

    /**
     * Critical error keywords to check
     */
    protected $criticalKeywords = [
        'PDOException',
        'ErrorException',
        'FatalErrorException',
        'FatalThrowableError',
        'QueryException',
        'TokenMismatchException',
        'AuthenticationException',
        'AuthorizationException',
        'HttpException',
        'ModelNotFoundException',
        'MethodNotAllowedHttpException',
        'NotFoundHttpException',
        'ValidationException',
    ];

    /**
     * Create the event listener.
     */
    public function __construct()
    {
        //
    }

    /**
     * Handle the event.
     */
    public function handle(EntryRecorded $event): void
    {
        $entry = $event->entry;

        // Exception 타입만 처리
        if ($entry->type !== 'exception') {
            return;
        }

        // Critical error인지 확인
        if (!$this->isCriticalError($entry)) {
            return;
        }

        // 알림 데이터 준비
        $errorData = [
            'telescope_uuid' => $entry->uuid,
            'exception_class' => $entry->content['class'] ?? 'Unknown',
            'message' => $entry->content['message'] ?? 'No message',
            'file' => $entry->content['file'] ?? 'Unknown',
            'line' => $entry->content['line'] ?? 0,
            'url' => request()->fullUrl(),
            'user_id' => auth()->id(),
            'user_name' => auth()->user()?->name,
            'occurred_at' => now()->format('Y-m-d H:i:s'),
        ];

        // Super Admin들에게 알림 전송
        $admins = User::role('super_admin')->get();
        
        if ($admins->isNotEmpty()) {
            Notification::send($admins, new CriticalErrorNotification($errorData));
        }

        // 로그 기록
        logger()->critical('Critical error detected', $errorData);
    }

    /**
     * Determine if the error is critical.
     */
    protected function isCriticalError($entry): bool
    {
        $exceptionClass = $entry->content['class'] ?? '';

        // Critical keywords 확인
        foreach ($this->criticalKeywords as $keyword) {
            if (str_contains($exceptionClass, $keyword)) {
                return true;
            }
        }

        // 500 에러 확인
        if (isset($entry->content['status']) && $entry->content['status'] >= 500) {
            return true;
        }

        return false;
    }
}
