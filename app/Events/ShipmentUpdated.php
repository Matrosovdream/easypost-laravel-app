<?php

namespace App\Events;

use App\Models\Shipment;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class ShipmentUpdated implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(public Shipment $shipment) {}

    public function broadcastOn(): array
    {
        return [new PrivateChannel("team.{$this->shipment->team_id}")];
    }

    public function broadcastAs(): string
    {
        return 'shipment.updated';
    }

    public function broadcastWith(): array
    {
        return [
            'id' => $this->shipment->id,
            'status' => $this->shipment->status,
            'tracking_code' => $this->shipment->tracking_code,
            'carrier' => $this->shipment->carrier,
        ];
    }
}
