<?php

namespace App\Repositories\Infra;

use App\Models\NotificationEvent;
use Illuminate\Support\Facades\Schema;

class NotificationEventRepo
{
    public function tableExists(): bool
    {
        return Schema::hasTable('notification_events');
    }

    public function record(array $data): NotificationEvent
    {
        return NotificationEvent::create($data);
    }
}
