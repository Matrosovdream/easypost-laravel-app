<?php

namespace App\Actions\Clients;

use App\Models\Client;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class GenerateClientInvoiceAction
{
    /**
     * Returns an in-memory invoice draft — no `invoices` table yet (P1).
     * Aggregates delivered/purchased shipments within the window, applies
     * FlexRate markup, and returns line items plus totals.
     */
    public function execute(Client $client, Carbon $from, Carbon $to): array
    {
        $shipments = DB::table('shipments')
            ->where('team_id', $client->team_id)
            ->where('client_id', $client->id)
            ->whereBetween('created_at', [$from, $to])
            ->whereIn('status', ['purchased', 'packed', 'delivered'])
            ->whereNotNull('cost_cents')
            ->select('id', 'reference', 'carrier', 'service', 'cost_cents', 'tracking_code', 'created_at')
            ->orderBy('created_at')
            ->get();

        $markup = (float) $client->flexrate_markup_pct;
        $perService = $client->per_service_markups ?? [];

        $lines = $shipments->map(function ($s) use ($markup, $perService) {
            $effectivePct = $markup;
            if (isset($perService[$s->service])) {
                $effectivePct = (float) $perService[$s->service];
            }
            $markupCents = (int) round(((float) $s->cost_cents) * ($effectivePct / 100));
            return [
                'shipment_id' => $s->id,
                'reference' => $s->reference,
                'carrier' => $s->carrier,
                'service' => $s->service,
                'tracking_code' => $s->tracking_code,
                'carrier_cost_cents' => (int) $s->cost_cents,
                'markup_pct' => $effectivePct,
                'markup_cents' => $markupCents,
                'charge_cents' => (int) $s->cost_cents + $markupCents,
                'created_at' => $s->created_at,
            ];
        })->values();

        return [
            'client_id' => $client->id,
            'company_name' => $client->company_name,
            'period' => ['from' => $from->toIso8601String(), 'to' => $to->toIso8601String()],
            'lines' => $lines,
            'totals' => [
                'count' => $lines->count(),
                'carrier_cost_cents' => (int) $lines->sum('carrier_cost_cents'),
                'markup_cents' => (int) $lines->sum('markup_cents'),
                'charge_cents' => (int) $lines->sum('charge_cents'),
            ],
        ];
    }
}
