<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Str;
use Illuminate\Support\Facades\Storage;

class Message extends Model
{
    use HasFactory;

    protected $fillable = ['sender_id', 'receiver_id', 'group_id', 'content', 'attachment', 'status', 'read_at'];


    public function sender()
    {
        return $this->belongsTo(User::class, 'sender_id');
    }

    public function receiver()
    {
        return $this->belongsTo(User::class, 'receiver_id');
    }

    public function group()
    {
        return $this->belongsTo(Group::class);
    }

    // Accessor pour obtenir l'URL du fichier attaché

    public function getAttachmentUrlAttribute()
    {
        if ($this->attachment) {
            return Str::isUrl($this->attachment, ['http', 'https']) ? $this->attachment : Storage::disk('public')->url($this->attachment);
        }

        return null;
    }
}
