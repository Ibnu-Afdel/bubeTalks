<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Comment extends Model
{
    /** @use HasFactory<\Database\Factories\CommentFactory> */
    use HasFactory;

    protected $fillable = [
        'user_id',
        'bube_message_id',
        'comment_text',
    ];
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
    public function bubeMessage(): BelongsTo
    {
        return $this->belongsTo(BubeMessage::class);
    }
}
