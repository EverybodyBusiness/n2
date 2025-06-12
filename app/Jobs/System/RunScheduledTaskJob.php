<?php

namespace App\Jobs\System;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use App\Models\System\ScheduledTask;
use App\Models\System\ScheduledTaskLog;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Log;

class RunScheduledTaskJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * 작업이 처리될 큐
     *
     * @var string
     */
    public $queue = 'schedules';

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
    public $timeout = 3600; // 기본 1시간

    /**
     * 스케줄 작업
     *
     * @var ScheduledTask
     */
    protected $task;

    /**
     * 실행 로그
     *
     * @var ScheduledTaskLog
     */
    protected $log;

    /**
     * Create a new job instance.
     */
    public function __construct(ScheduledTask $task, ScheduledTaskLog $log)
    {
        $this->task = $task;
        $this->log = $log;
        
        // 작업별 타임아웃 설정
        if ($task->max_runtime) {
            $this->timeout = $task->max_runtime;
        }
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $startTime = microtime(true);
        $startMemory = memory_get_usage();

        try {
            Log::info("스케줄 작업 실행 시작: {$this->task->name}", [
                'task_id' => $this->task->id,
                'log_id' => $this->log->id,
            ]);

            // 실행 상태 업데이트
            $this->log->update([
                'status' => 'running',
                'started_at' => now(),
            ]);

            $output = '';
            $exitCode = 0;

            // 작업 유형에 따라 실행
            switch ($this->task->type) {
                case 'command':
                    // Artisan 명령 실행
                    $exitCode = Artisan::call($this->task->command, $this->task->parameters ?? []);
                    $output = Artisan::output();
                    break;

                case 'job':
                    // Job 클래스 실행
                    $jobClass = $this->task->command;
                    $parameters = $this->task->parameters ?? [];
                    
                    if (class_exists($jobClass)) {
                        $job = new $jobClass(...$parameters);
                        dispatch_sync($job);
                        $output = "Job {$jobClass} 실행 완료";
                    } else {
                        throw new \Exception("Job 클래스를 찾을 수 없습니다: {$jobClass}");
                    }
                    break;

                case 'closure':
                    // 클로저 실행 (eval 사용 - 보안 주의)
                    if ($this->task->is_system) {
                        eval($this->task->command);
                        $output = "클로저 실행 완료";
                    } else {
                        throw new \Exception("시스템 작업이 아닌 경우 클로저 실행이 허용되지 않습니다.");
                    }
                    break;

                default:
                    throw new \Exception("알 수 없는 작업 유형: {$this->task->type}");
            }

            // 실행 완료
            $endTime = microtime(true);
            $endMemory = memory_get_usage();
            $duration = round($endTime - $startTime, 2);
            $memoryUsage = $endMemory - $startMemory;

            $this->log->update([
                'status' => $exitCode === 0 ? 'success' : 'failed',
                'finished_at' => now(),
                'duration' => $duration,
                'output' => $output,
                'memory_usage' => $memoryUsage,
            ]);

            // 모니터링 정보 업데이트
            $this->task->updateMonitor($exitCode === 0 ? 'success' : 'failed');

            Log::info("스케줄 작업 실행 완료: {$this->task->name}", [
                'task_id' => $this->task->id,
                'log_id' => $this->log->id,
                'duration' => $duration,
                'status' => $exitCode === 0 ? 'success' : 'failed',
            ]);

        } catch (\Exception $e) {
            $endTime = microtime(true);
            $duration = round($endTime - $startTime, 2);

            $this->log->update([
                'status' => 'failed',
                'finished_at' => now(),
                'duration' => $duration,
                'error_message' => $e->getMessage(),
                'output' => $e->getTraceAsString(),
            ]);

            // 모니터링 정보 업데이트
            $this->task->updateMonitor('failed');

            Log::error("스케줄 작업 실행 실패: {$this->task->name}", [
                'task_id' => $this->task->id,
                'log_id' => $this->log->id,
                'error' => $e->getMessage(),
            ]);

            throw $e;
        }
    }

    /**
     * 작업 실패 시 처리
     */
    public function failed(\Throwable $exception): void
    {
        $this->log->update([
            'status' => 'failed',
            'finished_at' => now(),
            'error_message' => $exception->getMessage(),
        ]);

        // 모니터링 정보 업데이트
        $this->task->updateMonitor('failed');

        Log::error("스케줄 작업 최종 실패: {$this->task->name}", [
            'task_id' => $this->task->id,
            'log_id' => $this->log->id,
            'error' => $exception->getMessage(),
        ]);
    }
} 