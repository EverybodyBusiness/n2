<?php

namespace App\Jobs\System;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use App\Models\User;
use Illuminate\Support\Facades\Log;
use Filament\Notifications\Notification;

class SendNotificationJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * 작업이 처리될 큐
     *
     * @var string
     */
    public $queue = 'notifications';

    /**
     * 작업 최대 시도 횟수
     *
     * @var int
     */
    public $tries = 3;

    /**
     * 작업 타임아웃 시간 (초)
     *
     * @var int
     */
    public $timeout = 30;

    /**
     * 알림 데이터
     *
     * @var array
     */
    protected $notificationData;

    /**
     * Create a new job instance.
     */
    public function __construct(array $notificationData)
    {
        $this->notificationData = $notificationData;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $userId = $this->notificationData['user_id'] ?? null;
        $type = $this->notificationData['type'] ?? 'general';
        $data = $this->notificationData['data'] ?? [];

        if (!$userId) {
            Log::warning('알림 발송 실패: 사용자 ID 없음', $this->notificationData);
            return;
        }

        $user = User::find($userId);
        if (!$user) {
            Log::warning('알림 발송 실패: 사용자를 찾을 수 없음', ['user_id' => $userId]);
            return;
        }

        // 알림 타입에 따른 처리
        switch ($type) {
            case 'report_completed':
                $this->sendReportCompletedNotification($user, $data);
                break;
            
            case 'task_assigned':
                $this->sendTaskAssignedNotification($user, $data);
                break;
            
            default:
                $this->sendGeneralNotification($user, $data);
                break;
        }

        Log::info('알림 발송 완료', [
            'user_id' => $userId,
            'type' => $type,
        ]);
    }

    /**
     * 리포트 완료 알림 발송
     */
    protected function sendReportCompletedNotification(User $user, array $data): void
    {
        // Database 알림으로 저장
        Notification::make()
            ->title('리포트 생성 완료')
            ->body($data['report_type'] . ' 리포트가 준비되었습니다.')
            ->success()
            ->sendToDatabase($user);
    }

    /**
     * 작업 할당 알림 발송
     */
    protected function sendTaskAssignedNotification(User $user, array $data): void
    {
        Notification::make()
            ->title('새 작업 할당')
            ->body('새로운 작업이 할당되었습니다: ' . ($data['task_name'] ?? ''))
            ->info()
            ->sendToDatabase($user);
    }

    /**
     * 일반 알림 발송
     */
    protected function sendGeneralNotification(User $user, array $data): void
    {
        Notification::make()
            ->title($data['title'] ?? '알림')
            ->body($data['body'] ?? '')
            ->sendToDatabase($user);
    }

    /**
     * 작업 실패 시 처리
     */
    public function failed(\Throwable $exception): void
    {
        Log::error('알림 발송 실패', [
            'notification_data' => $this->notificationData,
            'error' => $exception->getMessage(),
        ]);
    }
}
