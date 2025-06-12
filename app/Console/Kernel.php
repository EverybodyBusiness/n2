<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;
use App\Jobs\System\BackupJob;

class Kernel extends ConsoleKernel
{
    /**
     * Define the application's command schedule.
     */
    protected function schedule(Schedule $schedule): void
    {
        // Horizon 메트릭 스냅샷 (5분마다)
        $schedule->command('horizon:snapshot')->everyFiveMinutes();
        
        // 오래된 Horizon 데이터 정리 (매일 새벽 2시)
        $schedule->command('horizon:clear')->dailyAt('02:00');
        
        // 실패한 작업 정리 (7일 이상 된 것, 매주 일요일)
        $schedule->command('queue:prune-failed --hours=168')->weekly();
        
        // Telescope 에러 리포트 (기존 코드)
        $schedule->command('telescope:report daily')->daily();
        $schedule->command('telescope:report weekly')->weekly();
        $schedule->command('telescope:report monthly')->monthly();
        
        // Telescope 데이터 정리 (기존 코드)
        $schedule->command('telescope:prune --hours=24')->daily();
        
        // ===== 백업 스케줄 =====
        
        // 매일 새벽 3시에 전체 백업 (데이터베이스 + 파일)
        $schedule->job(new BackupJob())->dailyAt('03:00');
        
        // 매 6시간마다 데이터베이스만 백업
        $schedule->job(new BackupJob(['only-db' => true]))->everySixHours();
        
        // 백업 상태 모니터링 (매일 오전 9시)
        $schedule->command('backup:monitor')->dailyAt('09:00');
        
        // 오래된 백업 정리 (매일 새벽 4시)
        $schedule->command('backup:clean')->dailyAt('04:00');
        
        // 주간 백업 리포트 (매주 월요일 오전 8시)
        $schedule->command('backup:list')->weeklyOn(1, '08:00')
            ->emailOutputTo(env('BACKUP_NOTIFICATION_EMAIL', 'admin@example.com'));
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