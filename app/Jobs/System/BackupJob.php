<?php

namespace App\Jobs\System;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Log;

class BackupJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * 작업이 처리될 큐
     *
     * @var string
     */
    public $queue = 'backups';

    /**
     * 작업 최대 시도 횟수
     *
     * @var int
     */
    public $tries = 1;

    /**
     * 작업 타임아웃 시간 (초)
     *
     * @var int
     */
    public $timeout = 3600; // 1시간

    /**
     * 백업 옵션
     *
     * @var array
     */
    protected $options;

    /**
     * Create a new job instance.
     */
    public function __construct(array $options = [])
    {
        $this->options = array_merge([
            'only-db' => false,
            'only-files' => false,
            'disable-notifications' => false,
        ], $options);
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        Log::info('백업 작업 시작', $this->options);

        try {
            $command = 'backup:run';
            $arguments = [];

            if ($this->options['only-db']) {
                $arguments['--only-db'] = true;
            }

            if ($this->options['only-files']) {
                $arguments['--only-files'] = true;
            }

            if ($this->options['disable-notifications']) {
                $arguments['--disable-notifications'] = true;
            }

            // 백업 실행
            Artisan::call($command, $arguments);

            $output = Artisan::output();
            Log::info('백업 작업 완료', ['output' => $output]);

        } catch (\Exception $e) {
            Log::error('백업 작업 실패', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            throw $e;
        }
    }

    /**
     * 작업 실패 시 처리
     */
    public function failed(\Throwable $exception): void
    {
        Log::error('백업 작업 최종 실패', [
            'error' => $exception->getMessage(),
            'options' => $this->options,
        ]);

        // 관리자에게 알림 전송 (필요시 구현)
    }
} 