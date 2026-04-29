<?php

use App\Helpers\Trackers\TrackerHelper;
use App\Models\Tracker;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;

beforeEach(function () {
    $this->helper = new TrackerHelper();
});

function makeTracker(): Tracker
{
    $t = new Tracker([
        'tracking_code' => 'EZ123',
        'carrier' => 'USPS',
        'status' => 'in_transit',
        'status_detail' => 'arrival_scan',
        'public_url' => 'https://t.test',
        'shipment_id' => 7,
    ]);
    $t->id = 1;
    $t->est_delivery_date = now();
    $t->last_event_at = now();
    $t->created_at = now();
    return $t;
}

it('toListItem includes core tracker fields', function () {
    $t = makeTracker();
    $out = $this->helper->toListItem($t);
    expect($out)->toMatchArray([
        'id' => 1,
        'tracking_code' => 'EZ123',
        'carrier' => 'USPS',
        'status' => 'in_transit',
        'public_url' => 'https://t.test',
        'shipment_id' => 7,
    ]);
});

it('toDetail merges tracker events', function () {
    $t = makeTracker();
    $event = (object) [
        'status' => 'in_transit', 'status_detail' => 'departed',
        'message' => 'Departed facility', 'source' => 'EasyPost',
        'event_datetime' => now(), 'location' => ['city' => 'NYC'],
    ];
    $t->setRelation('events', new Collection([$event]));

    $out = $this->helper->toDetail($t);
    expect($out['events']->count())->toBe(1);
    expect($out['events'][0]['message'])->toBe('Departed facility');
});

it('toListPayload wraps a paginator', function () {
    $page = new LengthAwarePaginator([makeTracker()], 1, 25, 1);
    expect($this->helper->toListPayload($page)['meta']['total'])->toBe(1);
});
