<?php

use App\Helpers\Clients\InvoiceHelper;
use Illuminate\Support\Collection;

beforeEach(function () {
    $this->helper = new InvoiceHelper();
});

it('effectiveMarkupPct returns service-specific override when present', function () {
    expect($this->helper->effectiveMarkupPct(10.0, ['ground' => 15.0], 'ground'))->toBe(15.0);
});

it('effectiveMarkupPct falls back to default when service has no override', function () {
    expect($this->helper->effectiveMarkupPct(10.0, ['ground' => 15.0], 'priority'))->toBe(10.0);
});

it('effectiveMarkupPct falls back to default when service is null', function () {
    expect($this->helper->effectiveMarkupPct(10.0, ['ground' => 15.0], null))->toBe(10.0);
});

it('buildInvoiceLine computes markup correctly with default pct', function () {
    $shipment = (object) [
        'id' => 1,
        'reference' => 'R',
        'carrier' => 'USPS',
        'service' => 'priority',
        'tracking_code' => 'TC',
        'cost_cents' => 1000,
        'created_at' => '2026-01-01',
    ];

    $line = $this->helper->buildInvoiceLine($shipment, 10.0, []);

    expect($line)->toMatchArray([
        'shipment_id' => 1,
        'carrier_cost_cents' => 1000,
        'markup_pct' => 10.0,
        'markup_cents' => 100,
        'charge_cents' => 1100,
    ]);
});

it('buildInvoiceLine uses per-service markup when present', function () {
    $shipment = (object) [
        'id' => 2, 'reference' => null, 'carrier' => 'X', 'service' => 'ground',
        'tracking_code' => null, 'cost_cents' => 2000, 'created_at' => null,
    ];
    $line = $this->helper->buildInvoiceLine($shipment, 10.0, ['ground' => 25.0]);

    expect($line['markup_pct'])->toBe(25.0);
    expect($line['markup_cents'])->toBe(500);
    expect($line['charge_cents'])->toBe(2500);
});

it('buildInvoiceLine rounds markup to nearest cent', function () {
    $shipment = (object) [
        'id' => 3, 'reference' => null, 'carrier' => 'X', 'service' => null,
        'tracking_code' => null, 'cost_cents' => 333, 'created_at' => null,
    ];
    // 333 * 12.5% = 41.625 → rounds to 42
    $line = $this->helper->buildInvoiceLine($shipment, 12.5, []);
    expect($line['markup_cents'])->toBe(42);
    expect($line['charge_cents'])->toBe(375);
});

it('summarizeTotals sums lines correctly', function () {
    $lines = collect([
        ['carrier_cost_cents' => 1000, 'markup_cents' => 100, 'charge_cents' => 1100],
        ['carrier_cost_cents' => 2000, 'markup_cents' => 500, 'charge_cents' => 2500],
    ]);
    $totals = $this->helper->summarizeTotals($lines);
    expect($totals)->toBe([
        'count' => 2,
        'carrier_cost_cents' => 3000,
        'markup_cents' => 600,
        'charge_cents' => 3600,
    ]);
});

it('summarizeTotals returns zeros for empty', function () {
    $totals = $this->helper->summarizeTotals(new Collection());
    expect($totals)->toBe([
        'count' => 0, 'carrier_cost_cents' => 0, 'markup_cents' => 0, 'charge_cents' => 0,
    ]);
});

it('exposes BILLABLE_STATUSES constant', function () {
    expect(InvoiceHelper::BILLABLE_STATUSES)->toBe(['purchased', 'packed', 'delivered']);
});
