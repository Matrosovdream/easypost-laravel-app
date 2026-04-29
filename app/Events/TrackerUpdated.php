<?php

namespace App\Events;

use App\Models\Tracker;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class TrackerUpdated implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(public Tracker $tracker) {}

    public function broadcastOn(): array
    {
        return [new PrivateChannel("team.{$this->tracker->team_id}")];
    }

    public function broadcastAs(): string
    {
        return 'tracker.updated';
    }

    public function broadcastWith(): array
    {
        return [
            'id' => $this->tracker->id,
            'tracking_code' => $this->tracker->tracking_code,
            'carrier' => $this->tracker->carrier,
            'status' => $this->tracker->status,
            'status_detail' => $this->tracker->status_detail,
        ];
    }
}
