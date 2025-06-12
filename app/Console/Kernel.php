<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

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