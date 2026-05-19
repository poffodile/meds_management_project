<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ClientPortalMessage extends Model
{
    protected $table = 'client_portal_messages';

    protected $fillable = [
        'home_id',
        'client_id',
        'sender_type',
        'sender_id',
        'sender_name',
        'recipient_type',
        'recipient_id',
        'subject',
        'message_content',
        'priority',
        'category',
        'is_read',
        'read_at',
        'read_by',
        'replied_to_id',
        'status',
        'is_deleted',
        'created_by',
    ];

    protected $casts = [
        'is_read' => 'boolean',
        'is_deleted' => 'boolean',
        'read_at' => 'datetime',
    ];

    public function client()
    {
        return $this->belongsTo(\App\ServiceUser::class, 'client_id');
    }

    public function repliedTo()
    {
        return $this->belongsTo(self::class, 'replied_to_id');
    }

    public function replies()
    {
        return $this->hasMany(self::class, 'replied_to_id');
    }

    public function scopeForHome($query, $homeId)
    {
        return $query->where('home_id', $homeId);
    }

    public function scopeForClient($query, $clientId)
    {
        return $query->where('client_id', $clientId);
    }

    public function scopeActive($query)
    {
        return $query->where('is_deleted', 0);
    }

    public function scopeUnread($query)
    {
        return $query->where('is_read', 0);
    }
}
