<?php

namespace App\Events;

use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

/**
 * Nudges the dashboard to refresh sidebar counts. Payload is intentionally tiny —
 * the frontend refetches /api/navigation/counts on receipt.
 */
class CountsUpdated implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(public int $teamId) {}

    public function broadcastOn(): array
    {
        return [new PrivateChannel("team.{$this->teamId}")];
    }

    public function broadcastAs(): string
    {
        return 'counts.updated';
    }

    public function broadcastWith(): array
    {
        return ['team_id' => $this->teamId];
    }
}
