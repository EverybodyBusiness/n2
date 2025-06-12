<?php

namespace App\Models\System;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Cron\CronExpression;
use Carbon\Carbon;
use Illuminate\Support\Facades\Artisan;
use App\Jobs\System\RunScheduledTaskJob;

class ScheduledTask extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'name',
        'command',
        'type',
        'expression',
        'timezone',
        'description',
        'parameters',
        'is_active',
        'is_system',
        'notification_email',
        'max_runtime',
        'category',
        'without_overlapping',
        'run_in_background',
    ];

    protected $casts = [
        'parameters' => 'array',
        'is_active' => 'boolean',
        'is_system' => 'boolean',
        'without_overlapping' => 'boolean',
        'run_in_background' => 'boolean',
    ];

    /**
     * 실행 로그 관계
     */
    public function logs(): HasMany
    {
        return $this->hasMany(ScheduledTaskLog::class);
    }

    /**
     * 모니터링 정보 관계
     */
    public function monitor(): HasOne
    {
        return $this->hasOne(ScheduledTaskMonitor::class);
    }

    /**
     * 최근 로그
     */
    public function latestLog(): HasOne
    {
        return $this->hasOne(ScheduledTaskLog::class)->latestOfMany();
    }

    /**
     * 활성화된 작업만 조회
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * 카테고리별 조회
     */
    public function scopeByCategory($query, $category)
    {
        return $query->where('category', $category);
    }

    /**
     * 다음 실행 시간 계산
     */
    public function getNextRunTime(): ?Carbon
    {
        if (!$this->is_active) {
            return null;
        }

        try {
            $cron = new CronExpression($this->expression);
            return Carbon::instance($cron->getNextRunDate('now', 0, false, $this->timezone));
        } catch (\Exception $e) {
            return null;
        }
    }

    /**
     * 이전 실행 시간 계산
     */
    public function getPreviousRunTime(): ?Carbon
    {
        try {
            $cron = new CronExpression($this->expression);
            return Carbon::instance($cron->getPreviousRunDate('now', 0, false, $this->timezone));
        } catch (\Exception $e) {
            return null;
        }
    }

    /**
     * 오늘 실행 예정 시간 목록
     */
    public function getTodayRuns(): array
    {
        if (!$this->is_active) {
            return [];
        }

        $runs = [];
        $cron = new CronExpression($this->expression);
        $current = now($this->timezone)->startOfDay();
        $endOfDay = now($this->timezone)->endOfDay();

        while ($current <= $endOfDay) {
            $next = Carbon::instance($cron->getNextRunDate($current, 0, false, $this->timezone));
            if ($next <= $endOfDay) {
                $runs[] = $next;
                $current = $next->addMinute();
            } else {
                break;
            }
        }

        return $runs;
    }

    /**
     * 내일 실행 예정 시간 목록
     */
    public function getTomorrowRuns(): array
    {
        if (!$this->is_active) {
            return [];
        }

        $runs = [];
        $cron = new CronExpression($this->expression);
        $current = now($this->timezone)->addDay()->startOfDay();
        $endOfDay = now($this->timezone)->addDay()->endOfDay();

        while ($current <= $endOfDay) {
            $next = Carbon::instance($cron->getNextRunDate($current, 0, false, $this->timezone));
            if ($next <= $endOfDay) {
                $runs[] = $next;
                $current = $next->addMinute();
            } else {
                break;
            }
        }

        return $runs;
    }

    /**
     * 크론 표현식을 사람이 읽기 쉬운 형태로 변환
     */
    public function getHumanReadableExpression(): string
    {
        $expression = $this->expression;
        
        // 일반적인 패턴 매칭
        $patterns = [
            '0 0 * * *' => '매일 자정',
            '0 */6 * * *' => '6시간마다',
            '*/5 * * * *' => '5분마다',
            '0 9 * * 1' => '매주 월요일 오전 9시',
            '0 3 * * *' => '매일 새벽 3시',
            '0 2 * * *' => '매일 새벽 2시',
            '0 4 * * *' => '매일 새벽 4시',
            '0 9 * * *' => '매일 오전 9시',
            '0 0 * * 0' => '매주 일요일 자정',
        ];

        return $patterns[$expression] ?? $expression;
    }

    /**
     * 작업 즉시 실행
     */
    public function runNow(string $triggeredBy = 'manual', ?int $userId = null): ScheduledTaskLog
    {
        // 실행 로그 생성
        $log = $this->logs()->create([
            'started_at' => now(),
            'status' => 'running',
            'triggered_by' => $triggeredBy,
            'user_id' => $userId ?? auth()->id(),
        ]);

        // 백그라운드로 실행
        RunScheduledTaskJob::dispatch($this, $log);

        return $log;
    }

    /**
     * 실행 로그 기록
     */
    public function logExecution(string $status, array $data = []): ScheduledTaskLog
    {
        $log = $this->logs()->create(array_merge([
            'started_at' => now(),
            'finished_at' => $status !== 'running' ? now() : null,
            'status' => $status,
            'triggered_by' => 'schedule',
        ], $data));

        // 모니터링 정보 업데이트
        $this->updateMonitor($status);

        return $log;
    }

    /**
     * 모니터링 정보 업데이트
     */
    protected function updateMonitor(string $status): void
    {
        $monitor = $this->monitor()->firstOrCreate([
            'scheduled_task_id' => $this->id,
        ]);

        if ($status === 'success') {
            $monitor->update([
                'last_success_at' => now(),
                'current_consecutive_failures' => 0,
                'is_healthy' => true,
                'health_check_message' => null,
            ]);
        } elseif ($status === 'failed') {
            $failures = $monitor->current_consecutive_failures + 1;
            $isHealthy = $failures < $monitor->max_consecutive_failures;
            
            $monitor->update([
                'last_failure_at' => now(),
                'current_consecutive_failures' => $failures,
                'is_healthy' => $isHealthy,
                'health_check_message' => !$isHealthy ? "연속 {$failures}회 실패" : null,
            ]);
        }

        // 다음 실행 시간 업데이트
        $monitor->update([
            'next_run_at' => $this->getNextRunTime(),
        ]);
    }

    /**
     * 활성화/비활성화 토글
     */
    public function toggleActive(): bool
    {
        $this->update(['is_active' => !$this->is_active]);
        return $this->is_active;
    }

    /**
     * 크론 표현식 유효성 검사
     */
    public static function isValidExpression(string $expression): bool
    {
        try {
            new CronExpression($expression);
            return true;
        } catch (\Exception $e) {
            return false;
        }
    }
} 