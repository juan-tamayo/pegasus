<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class JobApplication extends Model
{
    protected $fillable = ['user_id', 'mailbox_connection_id', 'company', 'role', 'detected_status', 'manual_status', 'review_required', 'is_demo', 'applied_at', 'last_message_at', 'last_response_at'];

    protected function casts(): array
    {
        return ['review_required' => 'boolean', 'is_demo' => 'boolean', 'applied_at' => 'datetime', 'last_message_at' => 'datetime', 'last_response_at' => 'datetime'];
    }

    public function mailbox(): BelongsTo
    {
        return $this->belongsTo(MailboxConnection::class, 'mailbox_connection_id');
    }

    public function events(): HasMany
    {
        return $this->hasMany(ApplicationEvent::class)->orderBy('occurred_at');
    }

    public function summary(): array
    {
        $status = $this->manual_status ?? $this->detected_status;

        return array_merge($this->only('id', 'company', 'role', 'detected_status', 'manual_status', 'review_required', 'is_demo'), [
            'status' => $status,
            'applied_at' => $this->applied_at?->toISOString(),
            'last_message_at' => $this->last_message_at?->toISOString(),
            'waiting_days' => $status === 'awaiting_response' && $this->applied_at ? max(0, (int) $this->applied_at->diffInDays(now(), false)) : null,
            'mailbox' => $this->mailbox?->only('id', 'address', 'provider'),
        ]);
    }
}
