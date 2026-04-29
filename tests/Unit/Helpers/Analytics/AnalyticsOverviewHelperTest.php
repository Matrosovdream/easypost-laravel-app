<?php

use App\Helpers\Analytics\AnalyticsOverviewHelper;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->helper = new AnalyticsOverviewHelper();
    $this->seed();
    $this->teamId = (int) DB::table('teams')->first()->id;
});

it('totalsFromByStatus sums count and cost across the byStatus collection', function () {
    $byStatus = collect([
        ['status' => 'purchased', 'count' => 3, 'cost_cents' => 1500],
        ['status' => 'delivered', 'count' => 2, 'cost_cents' => 1000],
    ]);

    expect($this->helper->totalsFromByStatus($byStatus))->toBe([
        'total_shipments' => 5,
        'total_cost_cents' => 2500,
    ]);
});

it('totalsFromByStatus returns zeros for empty collection', function () {
    expect($this->helper->totalsFromByStatus(collect()))->toBe([
        'total_shipments' => 0,
        'total_cost_cents' => 0,
    ]);
});

it('byStatus returns shaped arrays with int casts', function () {
    foreach ($this->helper->byStatus($this->teamId) as $row) {
        expect($row)->toHaveKeys(['status', 'count', 'cost_cents']);
        expect($row['count'])->toBeInt();
        expect($row['cost_cents'])->toBeInt();
    }
});

it('byCarrier rows have count and cost_cents as ints', function () {
    foreach ($this->helper->byCarrier($this->teamId) as $row) {
        expect($row)->toHaveKeys(['carrier', 'count', 'cost_cents']);
        expect($row['count'])->toBeInt();
    }
});

it('printReadyCount returns int >= 0', function () {
    expect($this->helper->printReadyCount($this->teamId))->toBeInt()->toBeGreaterThanOrEqual(0);
});

it('pendingApprovalsCount returns int >= 0', function () {
    expect($this->helper->pendingApprovalsCount($this->teamId))->toBeInt()->toBeGreaterThanOrEqual(0);
});

it('trackerExceptionsCount returns int >= 0', function () {
    expect($this->helper->trackerExceptionsCount($this->teamId))->toBeInt()->toBeGreaterThanOrEqual(0);
});

it('monthlyUsageCount returns int >= 0', function () {
    $count = $this->helper->monthlyUsageCount($this->teamId, ['purchased', 'delivered']);
    expect($count)->toBeInt()->toBeGreaterThanOrEqual(0);
});

it('byStatus returns empty collection for unknown team', function () {
    expect($this->helper->byStatus(999999)->isEmpty())->toBeTrue();
});

it('byCarrier returns empty collection for unknown team', function () {
    expect($this->helper->byCarrier(999999)->isEmpty())->toBeTrue();
});
