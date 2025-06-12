<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;
use App\Jobs\System\BackupJob;
use App\Models\System\ScheduledTask;
use Cron\CronExpression;
use App\Jobs\System\RunScheduledTaskJob;
use Illuminate\Support\Facades\Log;

class Kernel extends ConsoleKernel
{
    /**
     * Define the application's command schedule.
     */
    protected function schedule(Schedule $schedule): void
    {
        // 하드코딩된 시스템 스케줄
        $this->scheduleSystemTasks($schedule);
        
        // DB 기반 동적 스케줄
        $this->scheduleDatabaseTasks($schedule);
    }
    
    /**
     * 시스템 스케줄 정의 (하드코딩)
     */
    protected function scheduleSystemTasks(Schedule $schedule): void
    {
        // Horizon 메트릭 스냅샷
        $schedule->command('horizon:snapshot')->everyFiveMinutes();
        
        // Horizon 일일 정리
        $schedule->command('horizon:clear')->dailyAt('02:00');
        
        // 실패한 작업 정리 (7일 이상 된 것)
        $schedule->command('queue:prune-failed --hours=168')->weekly();
        
        // Telescope 일일 보고서
        $schedule->command('telescope:report daily')->dailyAt('09:00');
        
        // Telescope 주간 보고서
        $schedule->command('telescope:report weekly')->weeklyOn(1, '09:00');
        
        // Telescope 월간 보고서
        $schedule->command('telescope:report monthly')->monthlyOn(1, '09:00');
        
        // Telescope 데이터 정리 (24시간 이상 된 데이터)
        $schedule->command('telescope:prune --hours=24')->daily();
        
        // 백업 작업들
        $schedule->command('backup:run')->dailyAt('03:00');
        $schedule->command('backup:run --only-db')->everyFourHours();
        $schedule->command('backup:monitor')->dailyAt('09:00');
        $schedule->command('backup:clean')->dailyAt('04:00');
        $schedule->command('backup:report')->weeklyOn(1, '08:00');
    }
    
    /**
     * DB에서 활성화된 스케줄 작업을 로드하여 등록
     */
    protected function scheduleDatabaseTasks(Schedule $schedule): void
    {
        try {
            // DB에서 활성화된 스케줄 작업 조회
            $tasks = ScheduledTask::active()->get();
            
            foreach ($tasks as $task) {
                $this->scheduleTask($schedule, $task);
            }
        } catch (\Exception $e) {
            // DB 연결 실패 시 로그만 남기고 계속 진행
            Log::error('DB 기반 스케줄 로드 실패: ' . $e->getMessage());
        }
    }
    
    /**
     * 개별 작업 스케줄링
     */
    protected function scheduleTask(Schedule $schedule, ScheduledTask $task): void
    {
        try {
            // 스케줄 이벤트 생성
            $event = $schedule->call(function () use ($task) {
                // 실행 로그 생성
                $log = $task->logs()->create([
                    'started_at' => now(),
                    'status' => 'pending',
                    'triggered_by' => 'schedule',
                ]);
                
                // 백그라운드 실행 여부에 따라 처리
                if ($task->run_in_background) {
                    // 큐를 통해 백그라운드 실행
                    RunScheduledTaskJob::dispatch($task, $log);
                } else {
                    // 동기적으로 실행
                    RunScheduledTaskJob::dispatchSync($task, $log);
                }
            });
            
            // 크론 표현식 설정
            $event->cron($task->expression);
            
            // 시간대 설정
            if ($task->timezone) {
                $event->timezone($task->timezone);
            }
            
            // 중복 실행 방지
            if ($task->without_overlapping) {
                $event->withoutOverlapping();
            }
            
            // 작업 이름 설정
            $event->name("scheduled_task_{$task->id}");
            $event->description($task->name);
            
            // 실행 조건 (활성화 상태 재확인)
            $event->when(function () use ($task) {
                return $task->fresh()->is_active;
            });
            
        } catch (\Exception $e) {
            Log::error("스케줄 작업 등록 실패: {$task->name}", [
                'task_id' => $task->id,
                'error' => $e->getMessage(),
            ]);
        }
    }
    
    /**
     * 스케줄 실행 로그 기록
     */
    protected function logScheduleExecution(string $command, string $status): void
    {
        try {
            // 하드코딩된 스케줄도 DB에 로그 기록
            $task = ScheduledTask::firstOrCreate(
                ['command' => $command],
                [
                    'name' => $this->generateTaskName($command),
                    'type' => $this->detectTaskType($command),
                    'expression' => '* * * * *', // 실제 크론은 코드에서 관리
                    'description' => 'System managed schedule',
                    'category' => $this->detectCategory($command),
                    'is_active' => true,
                ]
            );
            
            $task->logExecution($status);
        } catch (\Exception $e) {
            logger()->error('Failed to log schedule execution: ' . $e->getMessage());
        }
    }
    
    /**
     * 명령어에서 작업 이름 생성
     */
    protected function generateTaskName(string $command): string
    {
        $parts = explode(':', $command);
        return ucfirst(str_replace('-', ' ', end($parts)));
    }
    
    /**
     * 작업 유형 감지
     */
    protected function detectTaskType(string $command): string
    {
        return str_contains($command, 'Job') ? 'job' : 'command';
    }
    
    /**
     * 카테고리 감지
     */
    protected function detectCategory(string $command): string
    {
        if (str_contains($command, 'backup')) return 'backup';
        if (str_contains($command, 'horizon')) return 'monitoring';
        if (str_contains($command, 'telescope')) return 'monitoring';
        if (str_contains($command, 'queue')) return 'cleanup';
        if (str_contains($command, 'prune') || str_contains($command, 'clean')) return 'cleanup';
        return 'system';
    }

    /**
     * Register the commands for the application.
     */
    protected function commands(): void
    {
        $this->load(__DIR__.'/Commands');

        require base_path('routes/console.php');
    }
} 