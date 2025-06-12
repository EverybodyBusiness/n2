<?php

namespace App\Models\System;

use Spatie\Permission\Models\Role as SpatieRole;

class Role extends SpatieRole
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array<string>
     */
    protected $fillable = [
        'name',
        'title',
        'guard_name',
    ];

    /**
     * Get the display name for the role.
     *
     * @return string
     */
    public function getDisplayNameAttribute(): string
    {
        return $this->title ?: $this->name;
    }
}
