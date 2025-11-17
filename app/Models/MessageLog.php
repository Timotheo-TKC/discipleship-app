<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MessageLog extends Model
{
    /**
     * Indicates if the model should be timestamped.
     * We're using created_at only as specified in the plan.
     *
     * @var bool
     */
    public $timestamps = false;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'message_id',
        'recipient',
        'channel',
        'result',
        'response',
        'created_at',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'response' => 'array',
        'created_at' => 'datetime',
    ];

    /**
     * Get the message this log belongs to
     */
    public function message(): BelongsTo
    {
        return $this->belongsTo(Message::class);
    }

    /**
     * Check if the message was sent successfully
     */
    public function isSuccessful(): bool
    {
        return $this->result === 'success';
    }

    /**
     * Check if the message failed
     */
    public function isFailed(): bool
    {
        return $this->result === 'failed';
    }

    /**
     * Get the recipient's contact information
     */
    public function getRecipientContact(): string
    {
        return $this->recipient;
    }

    /**
     * Get formatted response for debugging
     */
    public function getFormattedResponse(): string
    {
        if (is_array($this->response)) {
            return json_encode($this->response, JSON_PRETTY_PRINT);
        }

        return $this->response ?? 'No response';
    }
}
