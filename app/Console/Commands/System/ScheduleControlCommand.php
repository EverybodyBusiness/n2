<?php

namespace App\Console\Commands\System;

use Illuminate\Console\Command;
use App\Models\System\ScheduledTask;

class ScheduleControlCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'schedule:control 
                            {action : The action to perform (list|enable|disable|run)}
                            {id? : The ID of the scheduled task}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = '스케줄 작업을 제어합니다';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $action = $this->argument('action');
        $id = $this->argument('id');

        switch ($action) {
            case 'list':
                $this->listSchedules();
                break;
                
            case 'enable':
                $this->enableSchedule($id);
                break;
                
            case 'disable':
                $this->disableSchedule($id);
                break;
                
            case 'run':
                $this->runSchedule($id);
                break;
                
            default:
                $this->error("알 수 없는 액션: {$action}");
                $this->info('사용 가능한 액션: list, enable, disable, run');
                return 1;
        }

        return 0;
    }

    /**
     * 스케줄 목록 표시
     */
    protected function listSchedules(): void
    {
        $schedules = ScheduledTask::with(['monitor', 'latestLog'])->get();

        if ($schedules->isEmpty()) {
            $this->info('등록된 스케줄이 없습니다.');
            return;
        }

        $headers = ['ID', '이름', '유형', '스케줄', '활성화', '상태', '다음 실행'];
        $rows = [];

        foreach ($schedules as $schedule) {
            $rows[] = [
                $schedule->id,
                $schedule->name,
                $schedule->type,
                $schedule->getHumanReadableExpression(),
                $schedule->is_active ? '✓' : '✗',
                $schedule->monitor?->is_healthy ? '정상' : '비정상',
                $schedule->getNextRunTime()?->format('Y-m-d H:i:s') ?? '-',
            ];
        }

        $this->table($headers, $rows);
    }

    /**
     * 스케줄 활성화
     */
    protected function enableSchedule($id): void
    {
        if (!$id) {
            $this->error('스케줄 ID를 지정해주세요.');
            return;
        }

        $schedule = ScheduledTask::find($id);

        if (!$schedule) {
            $this->error("ID {$id}에 해당하는 스케줄을 찾을 수 없습니다.");
            return;
        }

        if ($schedule->is_active) {
            $this->info("'{$schedule->name}' 스케줄은 이미 활성화되어 있습니다.");
            return;
        }

        $schedule->update(['is_active' => true]);
        $this->info("'{$schedule->name}' 스케줄이 활성화되었습니다.");
    }

    /**
     * 스케줄 비활성화
     */
    protected function disableSchedule($id): void
    {
        if (!$id) {
            $this->error('스케줄 ID를 지정해주세요.');
            return;
        }

        $schedule = ScheduledTask::find($id);

        if (!$schedule) {
            $this->error("ID {$id}에 해당하는 스케줄을 찾을 수 없습니다.");
            return;
        }

        if (!$schedule->is_active) {
            $this->info("'{$schedule->name}' 스케줄은 이미 비활성화되어 있습니다.");
            return;
        }

        $schedule->update(['is_active' => false]);
        $this->info("'{$schedule->name}' 스케줄이 비활성화되었습니다.");
    }

    /**
     * 스케줄 즉시 실행
     */
    protected function runSchedule($id): void
    {
        if (!$id) {
            $this->error('스케줄 ID를 지정해주세요.');
            return;
        }

        $schedule = ScheduledTask::find($id);

        if (!$schedule) {
            $this->error("ID {$id}에 해당하는 스케줄을 찾을 수 없습니다.");
            return;
        }

        $this->info("'{$schedule->name}' 스케줄을 실행합니다...");
        
        $log = $schedule->runNow('manual');
        
        $this->info("실행이 시작되었습니다. 로그 ID: {$log->id}");
        
        if ($schedule->run_in_background) {
            $this->info("백그라운드에서 실행 중입니다. 'php artisan horizon' 또는 Filament에서 진행 상황을 확인하세요.");
        } else {
            $this->info("실행이 완료될 때까지 대기 중...");
            
            // 실행 완료 대기 (최대 60초)
            $maxWait = 60;
            $waited = 0;
            
            while ($waited < $maxWait) {
                sleep(1);
                $waited++;
                
                $log->refresh();
                
                if (in_array($log->status, ['success', 'failed', 'timeout'])) {
                    break;
                }
            }
            
            if ($log->status === 'success') {
                $this->info("실행이 성공적으로 완료되었습니다.");
            } elseif ($log->status === 'failed') {
                $this->error("실행이 실패했습니다: " . $log->error_message);
            } else {
                $this->warn("실행 상태를 확인할 수 없습니다. Filament에서 확인해주세요.");
            }
        }
    }
} 