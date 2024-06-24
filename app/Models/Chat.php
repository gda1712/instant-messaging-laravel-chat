<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Message;

class Chat extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'is_group_chat'
    ];

    public function messages()
    {
        return $this->hasMany(Message::class);
    }

    public function users()
    {
        return $this->belongsToMany(User::class);
    }

    public function hasUser($user_id)
    {
        return $this->users->contains($user_id);
    }
}
