<?php

namespace App\Models\System;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Casts\AsArrayObject;
use App\Models\User;

class TelescopeEntry extends Model
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'telescope_entries';

    /**
     * The primary key for the model.
     *
     * @var string
     */
    protected $primaryKey = 'sequence';

    /**
     * The attributes that should be cast.
     *
     * @var array
     */
    protected $casts = [
        'content' => AsArrayObject::class,
        'created_at' => 'datetime',
    ];

    /**
     * Indicates if the model should be timestamped.
     *
     * @var bool
     */
    public $timestamps = false;

    /**
     * Scope a query to only include exceptions.
     */
    public function scopeExceptions($query)
    {
        return $query->where('type', 'exception');
    }

    /**
     * Scope a query to only include requests.
     */
    public function scopeRequests($query)
    {
        return $query->where('type', 'request');
    }

    /**
     * Scope a query to only include queries.
     */
    public function scopeQueries($query)
    {
        return $query->where('type', 'query');
    }

    /**
     * Scope a query to only include jobs.
     */
    public function scopeJobs($query)
    {
        return $query->where('type', 'job');
    }

    /**
     * Scope a query to only include logs.
     */
    public function scopeLogs($query)
    {
        return $query->where('type', 'log');
    }

    /**
     * Get the user that owns the entry.
     */
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    /**
     * Get exception class from content.
     */
    public function getExceptionClassAttribute()
    {
        return $this->content['class'] ?? 'Unknown';
    }

    /**
     * Get exception message from content.
     */
    public function getExceptionMessageAttribute()
    {
        return $this->content['message'] ?? 'No message';
    }

    /**
     * Get request method from content.
     */
    public function getRequestMethodAttribute()
    {
        return $this->content['method'] ?? 'GET';
    }

    /**
     * Get request URI from content.
     */
    public function getRequestUriAttribute()
    {
        return $this->content['uri'] ?? '/';
    }

    /**
     * Get response status from content.
     */
    public function getResponseStatusAttribute()
    {
        return $this->content['response_status'] ?? 200;
    }
}
