<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class EmailMessage extends Model
{
    protected $fillable = ['mailbox_connection_id', 'provider_message_key', 'provider_thread_id', 'sender', 'subject', 'received_at', 'processing_state'];

    protected function casts(): array
    {
        return ['received_at' => 'datetime'];
    }
}
