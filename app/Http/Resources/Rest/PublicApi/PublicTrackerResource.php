<?php

namespace App\Http\Resources\Rest\PublicApi;

use App\Models\Tracker;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin Tracker
 */
class PublicTrackerResource extends JsonResource
{
    public static $wrap = null;

    public function toArray(Request $request): array
    {
        $settings = $this->team?->settings ?? [];

        return [
            'code' => $this->tracking_code,
            'carrier' => $this->carrier,
            'status' => $this->status,
            'status_label' => $this->statusLabel(),
            'estimated_delivery_date' => $this->est_delivery_date?->toIso8601String(),
            'tenant' => [
                'name' => $settings['brand_name'] ?? $this->team?->name ?? 'ShipDesk',
                'logo_url' => $settings['brand_logo_url'] ?? null,
                'brand_color' => $settings['brand_color'] ?? null,
            ],
            'events' => $this->events->map(fn ($e) => [
                'status' => $e->status,
                'message' => $e->message,
                'location' => $this->formatLocation($e->location),
                'occurred_at' => $e->event_datetime?->toIso8601String(),
            ])->values(),
        ];
    }

    private function statusLabel(): string
    {
        return match ($this->status) {
            'pre_transit' => 'Labeled — awaiting carrier pickup',
            'in_transit' => 'In transit',
            'out_for_delivery' => 'Out for delivery',
            'delivered' => 'Delivered',
            'return_to_sender' => 'Return to sender',
            'failure' => 'Delivery failure',
            default => ucfirst(str_replace('_', ' ', (string) $this->status)),
        };
    }

    private function formatLocation(mixed $location): ?string
    {
        if (! is_array($location) || empty($location)) {
            return null;
        }
        $parts = array_filter([
            $location['city'] ?? null,
            $location['state'] ?? null,
            $location['country'] ?? null,
        ]);

        return $parts ? implode(', ', $parts) : null;
    }
}
