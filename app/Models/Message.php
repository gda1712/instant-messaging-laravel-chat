<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\User;
use App\Models\Chat;

class Message extends Model
{
    use HasFactory;

    public const TYPE_NORMAL = 'normal';
    public const TYPE_REMOVED_CHAT = 'removed_chat';

    protected $fillable = [
        'user_id',
        'chat_id',
        'message',
        'file',
        'type'
    ];

    protected $casts = [
        'message' => 'encrypted',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function chat()
    {
        return $this->belongsTo(Chat::class);
    }
}
