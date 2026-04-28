<?php

namespace App\Helpers\Clients;

use App\Models\Client;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class InvoiceHelper
{
    /** Statuses that count as billable when generating an invoice. */
    public const BILLABLE_STATUSES = ['purchased', 'packed', 'delivered'];

    /**
     * Fetch shipments eligible for invoicing within a window.
     */
    public function fetchBillableShipments(int $teamId, int $clientId, Carbon $from, Carbon $to): Collection
    {
        return DB::table('shipments')
            ->where('team_id', $teamId)
            ->where('client_id', $clientId)
            ->whereBetween('created_at', [$from, $to])
            ->whereIn('status', self::BILLABLE_STATUSES)
            ->whereNotNull('cost_cents')
            ->select('id', 'reference', 'carrier', 'service', 'cost_cents', 'tracking_code', 'created_at')
            ->orderBy('created_at')
            ->get();
    }

    /**
     * Resolve markup % for a given service, falling back to the client default.
     */
    public function effectiveMarkupPct(float $defaultPct, array $perService, ?string $service): float
    {
        if ($service !== null && isset($perService[$service])) {
            return (float) $perService[$service];
        }
        return $defaultPct;
    }

    /**
     * Build a single invoice line from a shipment row.
     */
    public function buildInvoiceLine(object $shipment, float $defaultPct, array $perService): array
    {
        $effectivePct = $this->effectiveMarkupPct($defaultPct, $perService, $shipment->service);
        $markupCents = (int) round(((float) $shipment->cost_cents) * ($effectivePct / 100));

        return [
            'shipment_id'        => $shipment->id,
            'reference'          => $shipment->reference,
            'carrier'            => $shipment->carrier,
            'service'            => $shipment->service,
            'tracking_code'      => $shipment->tracking_code,
            'carrier_cost_cents' => (int) $shipment->cost_cents,
            'markup_pct'         => $effectivePct,
            'markup_cents'       => $markupCents,
            'charge_cents'       => (int) $shipment->cost_cents + $markupCents,
            'created_at'         => $shipment->created_at,
        ];
    }

    /**
     * Build all invoice lines for a client over a window.
     */
    public function buildLines(Client $client, Carbon $from, Carbon $to): Collection
    {
        $shipments = $this->fetchBillableShipments($client->team_id, $client->id, $from, $to);
        $defaultPct = (float) $client->flexrate_markup_pct;
        $perService = $client->per_service_markups ?? [];

        return $shipments
            ->map(fn ($s) => $this->buildInvoiceLine($s, $defaultPct, $perService))
            ->values();
    }

    /**
     * Summarize totals from a collection of invoice lines.
     */
    public function summarizeTotals(Collection $lines): array
    {
        return [
            'count'              => $lines->count(),
            'carrier_cost_cents' => (int) $lines->sum('carrier_cost_cents'),
            'markup_cents'       => (int) $lines->sum('markup_cents'),
            'charge_cents'       => (int) $lines->sum('charge_cents'),
        ];
    }
}
