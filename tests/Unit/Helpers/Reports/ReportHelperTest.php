<?php

use App\Helpers\Reports\ReportHelper;
use Illuminate\Support\Collection;

beforeEach(function () {
    $this->helper = new ReportHelper();
});

it('toListItem maps stdClass row to array', function () {
    $row = (object) [
        'id' => 1, 'type' => 'shipment', 'status' => 'queued',
        'start_date' => '2026-01-01', 'end_date' => '2026-01-31',
        's3_key' => null, 'created_at' => '2026-01-01T00:00:00Z',
    ];
    expect($this->helper->toListItem($row))->toBe([
        'id' => 1, 'type' => 'shipment', 'status' => 'queued',
        'start_date' => '2026-01-01', 'end_date' => '2026-01-31',
        's3_key' => null, 'created_at' => '2026-01-01T00:00:00Z',
    ]);
});

it('toListPayload wraps a Collection', function () {
    $row = (object) [
        'id' => 1, 'type' => 'shipment', 'status' => 'done',
        'start_date' => null, 'end_date' => null, 's3_key' => 'k', 'created_at' => null,
    ];
    $rows = collect([$row]);
    $out = $this->helper->toListPayload($rows);
    expect($out['data']->count())->toBe(1);
    expect($out['data'][0]['s3_key'])->toBe('k');
});

it('toListPayload returns empty data array for empty collection', function () {
    expect($this->helper->toListPayload(new Collection())['data']->count())->toBe(0);
});
