<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class FeaturesPageVisited implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(
        public string $visitorId,
        public ?string $userAgent,
        public string $occurredAt,
    ) {}

    public function broadcastOn(): array
    {
        return [new Channel('demo.features')];
    }

    public function broadcastAs(): string
    {
        return 'features.visited';
    }

    public function broadcastWith(): array
    {
        return [
            'visitor_id'  => $this->visitorId,
            'user_agent'  => $this->userAgent,
            'occurred_at' => $this->occurredAt,
        ];
    }
}
