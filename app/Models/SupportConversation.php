<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class SupportConversation extends Model
{
    public const STATUS_OPEN = 'open';

    public const STATUS_CLOSED = 'closed';

    protected $fillable = [
        'vendor_id',
        'user_id',
        'status',
    ];

    /**
     * Open = vendor sent last message (awaiting admin). Closed = admin replied last.
     */
    public function resolveTicketStatus(): string
    {
        $last = $this->lastMessage();

        if (! $last || $last->sender_type === SupportMessage::SENDER_VENDOR) {
            return self::STATUS_OPEN;
        }

        return self::STATUS_CLOSED;
    }

    public function syncTicketStatusFromLastMessage(): void
    {
        $status = $this->resolveTicketStatus();

        if ($this->status !== $status) {
            $this->forceFill(['status' => $status])->save();
        }
    }

    public function isTicketOpen(): bool
    {
        return $this->resolveTicketStatus() === self::STATUS_OPEN;
    }

    public function lastMessage(): ?SupportMessage
    {
        if ($this->relationLoaded('messages') && $this->messages->isNotEmpty()) {
            return $this->messages->sortByDesc('id')->values()->first();
        }

        return $this->messages()->reorder()->latest('id')->first();
    }

    public function vendor(): BelongsTo
    {
        return $this->belongsTo(Vendor::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function messages(): HasMany
    {
        return $this->hasMany(SupportMessage::class);
    }

    public function orderedMessages(): HasMany
    {
        return $this->hasMany(SupportMessage::class)->orderBy('id');
    }
}
