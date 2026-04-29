<?php

namespace App\Repositories\Tracker;

use App\Models\TrackerEvent;
use App\Repositories\AbstractRepo;

class TrackerEventRepo extends AbstractRepo
{
    public function __construct()
    {
        $this->model = new TrackerEvent();
    }

    public function hasEvent(int $trackerId, string $at, string $status): bool
    {
        return TrackerEvent::where('tracker_id', $trackerId)
            ->where('event_datetime', $at)
            ->where('status', $status)
            ->exists();
    }

    public function record(int $trackerId, array $data): TrackerEvent
    {
        return TrackerEvent::create(array_merge($data, ['tracker_id' => $trackerId]));
    }
}
