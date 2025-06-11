<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

/*
|--------------------------------------------------------------------------
| Telescope Maintenance Schedules
|--------------------------------------------------------------------------
*/

// Telescope 데이터 정리 (매일 새벽 2시)
Schedule::command('telescope:prune')->daily()->at('02:00');

// Telescope 예외 데이터는 더 오래 보관 (7일)
Schedule::command('telescope:prune --hours=168')->daily()->at('02:30');

// 일일 에러 리포트 생성 (매일 오전 9시)
Schedule::command('telescope:report --period=daily')->dailyAt('09:00');

// 주간 에러 리포트 생성 (매주 월요일 오전 9시)
Schedule::command('telescope:report --period=weekly')->weeklyOn(1, '09:00');

// 월간 에러 리포트 생성 (매월 1일 오전 9시)
Schedule::command('telescope:report --period=monthly')->monthlyOn(1, '09:00');
