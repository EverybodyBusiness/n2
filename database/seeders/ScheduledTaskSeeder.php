<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\System\ScheduledTask;

class ScheduledTaskSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $tasks = [
            [
                'name' => '일일 백업 작업',
                'command' => 'backup:run',
                'type' => 'command',
                'expression' => '0 3 * * *',
                'timezone' => 'Asia/Seoul',
                'description' => '매일 새벽 3시에 전체 시스템 백업을 수행합니다.',
                'category' => 'backup',
                'is_active' => true,
                'is_system' => true,
                'without_overlapping' => true,
                'run_in_background' => true,
                'max_runtime' => 3600,
            ],
            [
                'name' => '데이터베이스 백업',
                'command' => 'backup:run --only-db',
                'type' => 'command',
                'expression' => '0 */6 * * *',
                'timezone' => 'Asia/Seoul',
                'description' => '6시간마다 데이터베이스만 백업합니다.',
                'category' => 'backup',
                'is_active' => true,
                'is_system' => true,
                'without_overlapping' => true,
                'run_in_background' => true,
                'max_runtime' => 600,
            ],
            [
                'name' => 'Telescope 일일 리포트',
                'command' => 'telescope:report daily',
                'type' => 'command',
                'expression' => '0 9 * * *',
                'timezone' => 'Asia/Seoul',
                'description' => '매일 오전 9시에 Telescope 일일 리포트를 생성합니다.',
                'category' => 'report',
                'is_active' => true,
                'is_system' => true,
                'without_overlapping' => false,
                'run_in_background' => false,
            ],
            [
                'name' => '오래된 로그 정리',
                'command' => 'log:clear',
                'type' => 'command',
                'expression' => '0 2 * * 0',
                'timezone' => 'Asia/Seoul',
                'description' => '매주 일요일 새벽 2시에 30일 이상 된 로그를 정리합니다.',
                'category' => 'cleanup',
                'is_active' => true,
                'is_system' => false,
                'without_overlapping' => true,
                'run_in_background' => true,
                'parameters' => ['days' => 30],
            ],
            [
                'name' => '실패한 작업 정리',
                'command' => 'queue:prune-failed',
                'type' => 'command',
                'expression' => '0 0 * * 0',
                'timezone' => 'Asia/Seoul',
                'description' => '매주 일요일 자정에 7일 이상 된 실패한 작업을 정리합니다.',
                'category' => 'cleanup',
                'is_active' => true,
                'is_system' => true,
                'without_overlapping' => false,
                'run_in_background' => false,
                'parameters' => ['hours' => 168],
            ],
            [
                'name' => '일일 통계 리포트 생성',
                'command' => 'App\\Jobs\\System\\GenerateDailyReportJob',
                'type' => 'job',
                'expression' => '0 8 * * *',
                'timezone' => 'Asia/Seoul',
                'description' => '매일 오전 8시에 일일 통계 리포트를 생성합니다.',
                'category' => 'report',
                'is_active' => false,
                'is_system' => false,
                'without_overlapping' => true,
                'run_in_background' => true,
                'notification_email' => 'admin@example.com',
            ],
            [
                'name' => '캐시 워밍업',
                'command' => 'cache:warmup',
                'type' => 'command',
                'expression' => '*/30 * * * *',
                'timezone' => 'Asia/Seoul',
                'description' => '30분마다 캐시를 미리 생성하여 성능을 향상시킵니다.',
                'category' => 'maintenance',
                'is_active' => false,
                'is_system' => false,
                'without_overlapping' => true,
                'run_in_background' => false,
            ],
        ];

        foreach ($tasks as $taskData) {
            $task = ScheduledTask::create($taskData);
            
            // 모니터링 정보 초기화
            $task->monitor()->create([
                'expected_runtime' => $taskData['max_runtime'] ?? null,
                'max_consecutive_failures' => 3,
                'current_consecutive_failures' => 0,
                'is_healthy' => true,
                'next_run_at' => $task->getNextRunTime(),
            ]);
        }
    }
} 