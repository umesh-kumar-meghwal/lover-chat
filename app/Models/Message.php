<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Message extends Model
{
    use HasFactory;

protected $fillable = ['sender_id', 'receiver_id', 'message', 'image', 'is_delivered', 'is_read', 'parent_id', 'reaction', 'is_pinned'];
    public function sender()
    {
        return $this->belongsTo(User::class, 'sender_id');
    }

    public function receiver()
    {
        return $this->belongsTo(User::class, 'receiver_id');
    }
    public function parent()
{
    return $this->belongsTo(Message::class, 'parent_id');
}
}
