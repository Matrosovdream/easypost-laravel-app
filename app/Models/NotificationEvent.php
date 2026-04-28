<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class NotificationEvent extends Model
{
    protected $fillable = [
        'team_id',
        'shipment_id',
        'tracker_id',
        'channel',
        'template',
        'recipient',
        'subject',
        'body_rendered_s3_key',
        'provider_message_id',
        'status',
        'error_message',
        'sent_at',
    ];

    protected $casts = [
        'sent_at' => 'datetime',
    ];
}
