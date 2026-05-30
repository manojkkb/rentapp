<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SupportMessage extends Model
{
    public const SENDER_VENDOR = 'vendor';

    public const SENDER_ADMIN = 'admin';

    protected $fillable = [
        'support_conversation_id',
        'sender_type',
        'sender_id',
        'body',
        'read_at',
    ];

    protected function casts(): array
    {
        return [
            'read_at' => 'datetime',
        ];
    }

    protected static function booted(): void
    {
        static::created(function (SupportMessage $message) {
            $conversation = SupportConversation::query()->find($message->support_conversation_id);
            $conversation?->syncTicketStatusFromLastMessage();
        });
    }

    public function conversation(): BelongsTo
    {
        return $this->belongsTo(SupportConversation::class, 'support_conversation_id');
    }

    /**
     * @return array<string, mixed>
     */
    public function toBroadcastArray(): array
    {
        return [
            'id' => $this->id,
            'sender_type' => $this->sender_type,
            'sender_id' => $this->sender_id,
            'body' => $this->body,
            'created_at' => $this->created_at?->toIso8601String(),
        ];
    }
}
