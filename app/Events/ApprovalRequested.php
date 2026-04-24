<?php

namespace App\Events;

use App\Models\Approval;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class ApprovalRequested implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(public Approval $approval) {}

    public function broadcastOn(): array
    {
        return [new PrivateChannel("team.{$this->approval->team_id}")];
    }

    public function broadcastAs(): string
    {
        return 'approval.requested';
    }

    public function broadcastWith(): array
    {
        return [
            'id' => $this->approval->id,
            'shipment_id' => $this->approval->shipment_id,
            'cost_cents' => $this->approval->cost_cents,
            'reason' => $this->approval->reason,
        ];
    }
}
