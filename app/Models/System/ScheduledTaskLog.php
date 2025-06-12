<?php

namespace App\Models\System;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Models\User;

class ScheduledTaskLog extends Model
{
    protected $fillable = [
        'scheduled_task_id',
        'started_at',
        'finished_at',
        'duration',
        'status',
        'output',
        'error_message',
        'memory_usage',
        'triggered_by',
        'user_id',
    ];

    protected $casts = [
        'started_at' => 'datetime',
        'finished_at' => 'datetime',
        'duration' => 'integer',
        'memory_usage' => 'integer',
    ];

    /**
     * 스케줄 작업 관계
     */
    public function scheduledTask(): BelongsTo
    {
        return $this->belongsTo(ScheduledTask::class);
    }

    /**
     * 실행한 사용자 관계
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * 실행 시간 계산
     */
    public function calculateDuration(): void
    {
        if ($this->started_at && $this->finished_at) {
            $this->duration = $this->started_at->diffInSeconds($this->finished_at);
            $this->save();
        }
    }

    /**
     * 상태별 스코프
     */
    public function scopeByStatus($query, $status)
    {
        return $query->where('status', $status);
    }

    /**
     * 최근 로그 스코프
     */
    public function scopeRecent($query, $days = 7)
    {
        return $query->where('started_at', '>=', now()->subDays($days));
    }
} 