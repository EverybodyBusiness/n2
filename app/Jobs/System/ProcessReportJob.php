<?php

namespace App\Jobs\System;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class ProcessReportJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * 작업이 처리될 큐
     *
     * @var string
     */
    public $queue = 'reports';

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
    public $timeout = 300;

    /**
     * 리포트 데이터
     *
     * @var array
     */
    protected $reportData;

    /**
     * Create a new job instance.
     */
    public function __construct(array $reportData)
    {
        $this->reportData = $reportData;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        Log::info('리포트 처리 시작', [
            'report_type' => $this->reportData['type'] ?? 'unknown',
            'user_id' => $this->reportData['user_id'] ?? null,
        ]);

        // 리포트 처리 로직
        $startTime = microtime(true);

        // 실제 리포트 생성 로직 (예시)
        sleep(2); // 시뮬레이션을 위한 지연

        $processingTime = microtime(true) - $startTime;

        Log::info('리포트 처리 완료', [
            'processing_time' => round($processingTime, 2) . 's',
            'report_type' => $this->reportData['type'] ?? 'unknown',
        ]);

        // 완료 알림 발송 (다른 큐로)
        if (isset($this->reportData['notify_user']) && $this->reportData['notify_user']) {
            dispatch(new SendNotificationJob([
                'user_id' => $this->reportData['user_id'],
                'type' => 'report_completed',
                'data' => [
                    'report_type' => $this->reportData['type'],
                    'completed_at' => now(),
                ],
            ]))->onQueue('notifications');
        }
    }

    /**
     * 작업 실패 시 처리
     */
    public function failed(\Throwable $exception): void
    {
        Log::error('리포트 처리 실패', [
            'report_type' => $this->reportData['type'] ?? 'unknown',
            'error' => $exception->getMessage(),
            'trace' => $exception->getTraceAsString(),
        ]);

        // 실패 알림 발송
        // notification logic here...
    }

    /**
     * 작업 재시도 전 대기 시간 계산
     *
     * @return array<int, int>
     */
    public function backoff(): array
    {
        return [10, 30, 60]; // 10초, 30초, 60초 후 재시도
    }
}
