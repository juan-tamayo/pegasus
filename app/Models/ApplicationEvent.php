<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ApplicationEvent extends Model
{
    protected $fillable = ['job_application_id', 'email_message_id', 'event_type', 'subject', 'evidence', 'occurred_at'];

    protected function casts(): array
    {
        return ['evidence' => 'encrypted', 'occurred_at' => 'datetime'];
    }
}
