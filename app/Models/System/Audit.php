<?php

namespace App\Models\System;

use OwenIt\Auditing\Models\Audit as AuditModel;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class Audit extends AuditModel
{
    /**
     * {@inheritdoc}
     */
    protected $table = 'audits';

    /**
     * {@inheritdoc}
     */
    protected $casts = [
        'old_values' => 'json',
        'new_values' => 'json',
        'created_at' => 'datetime',
    ];

    /**
     * Get the auditable entity.
     *
     * @return MorphTo
     */
    public function auditable(): MorphTo
    {
        return $this->morphTo();
    }

    /**
     * Get the user who performed the audit.
     *
     * @return MorphTo
     */
    public function user(): MorphTo
    {
        return $this->morphTo();
    }

    /**
     * Get the audit event name in Korean.
     *
     * @return string
     */
    public function getEventNameAttribute(): string
    {
        $eventNames = [
            'created' => '생성',
            'updated' => '수정',
            'deleted' => '삭제',
            'restored' => '복구',
        ];

        return $eventNames[$this->event] ?? $this->event;
    }

    /**
     * Get formatted created at.
     *
     * @return string
     */
    public function getFormattedCreatedAtAttribute(): string
    {
        return $this->created_at->format('Y-m-d H:i:s');
    }
} 