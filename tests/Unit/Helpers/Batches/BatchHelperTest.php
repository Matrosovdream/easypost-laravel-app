<?php

use App\Helpers\Batches\BatchHelper;
use App\Models\Batch;
use App\Models\User;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;

beforeEach(function () {
    $this->helper = new BatchHelper();
});

function makeBatch(array $attrs = []): Batch
{
    $b = new Batch(array_merge([
        'reference' => 'B-1',
        'state' => 'created',
        'num_shipments' => 3,
        'label_pdf_s3_key' => 's3://x.pdf',
    ], $attrs));
    $b->id = $attrs['id'] ?? 100;
    $b->created_at = $attrs['created_at'] ?? now();
    return $b;
}

it('toListItem includes creator when present', function () {
    $batch = makeBatch();
    $creator = new User(['name' => 'Stan']);
    $creator->id = 9;
    $batch->setRelation('creator', $creator);

    $out = $this->helper->toListItem($batch);

    expect($out)->toMatchArray([
        'id' => 100,
        'reference' => 'B-1',
        'state' => 'created',
        'num_shipments' => 3,
        'label_url' => 's3://x.pdf',
    ]);
    expect($out['created_by'])->toBe(['id' => 9, 'name' => 'Stan']);
});

it('toListItem returns null creator_by when missing', function () {
    $batch = makeBatch();
    $batch->setRelation('creator', null);

    expect($this->helper->toListItem($batch)['created_by'])->toBeNull();
});

it('toIdentity returns id+state only', function () {
    $batch = makeBatch();
    expect($this->helper->toIdentity($batch))->toBe(['id' => 100, 'state' => 'created']);
});

it('toLabelResult returns id+label_url only', function () {
    $batch = makeBatch();
    expect($this->helper->toLabelResult($batch))->toBe(['id' => 100, 'label_url' => 's3://x.pdf']);
});

it('toDetail includes shipments collection', function () {
    $batch = makeBatch();
    $batch->setRelation('creator', null);
    $batch->status_summary = ['ok' => 3];
    $batch->scan_form_id = null;
    $batch->pickup_id = null;
    $batch->setRelation('shipments', new Collection());

    $out = $this->helper->toDetail($batch);
    expect($out['status_summary'])->toBe(['ok' => 3]);
    expect($out['shipments']->count())->toBe(0);
});

it('toListPayload returns paginator-shaped payload', function () {
    $batch = makeBatch();
    $batch->setRelation('creator', null);
    $page = new LengthAwarePaginator([$batch], 1, 25, 1);

    $out = $this->helper->toListPayload($page);
    expect($out['data']->count())->toBe(1);
    expect($out['meta'])->toBe(['current_page' => 1, 'last_page' => 1, 'total' => 1]);
});
