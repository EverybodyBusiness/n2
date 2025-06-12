<?php

namespace App\Models\System;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ScheduledTaskMonitor extends Model
{
    protected $fillable = [
        'scheduled_task_id',
        'expected_runtime',
        'max_consecutive_failures',
        'current_consecutive_failures',
        'last_success_at',
        'last_failure_at',
        'next_run_at',
        'is_healthy',
        'health_check_message',
    ];

    protected $casts = [
        'expected_runtime' => 'integer',
        'max_consecutive_failures' => 'integer',
        'current_consecutive_failures' => 'integer',
        'last_success_at' => 'datetime',
        'last_failure_at' => 'datetime',
        'next_run_at' => 'datetime',
        'is_healthy' => 'boolean',
    ];

    /**
     * 스케줄 작업 관계
     */
    public function scheduledTask(): BelongsTo
    {
        return $this->belongsTo(ScheduledTask::class);
    }

    /**
     * 건강하지 않은 작업 스코프
     */
    public function scopeUnhealthy($query)
    {
        return $query->where('is_healthy', false);
    }

    /**
     * 건강 상태 확인
     */
    public function checkHealth(): bool
    {
        // 연속 실패 횟수 확인
        if ($this->current_consecutive_failures >= $this->max_consecutive_failures) {
            $this->update([
                'is_healthy' => false,
                'health_check_message' => "연속 {$this->current_consecutive_failures}회 실패로 비정상 상태",
            ]);
            return false;
        }

        // 예상 실행 시간 초과 확인
        if ($this->expected_runtime && $this->scheduledTask->latestLog) {
            $lastLog = $this->scheduledTask->latestLog;
            if ($lastLog->duration > $this->expected_runtime * 2) {
                $this->update([
                    'is_healthy' => false,
                    'health_check_message' => "실행 시간이 예상보다 2배 이상 초과",
                ]);
                return false;
            }
        }

        // 정상 상태
        $this->update([
            'is_healthy' => true,
            'health_check_message' => null,
        ]);

        return true;
    }
} 