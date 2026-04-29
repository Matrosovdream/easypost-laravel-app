<?php

use App\Actions\Shipments\ListMyQueueAction;
use App\Actions\Shipments\ListShipmentsAction;
use App\Helpers\Shipments\ShipmentListPayloadHelper;
use App\Models\User;
use App\Repositories\Shipping\ShipmentRepo;
use Illuminate\Pagination\LengthAwarePaginator;

it('ListShipmentsAction returns paginated payload with per_page', function () {
    $repo = mock(ShipmentRepo::class);
    $repo->shouldReceive('paginateScoped')->andReturn(new LengthAwarePaginator([], 0, 25, 1));

    $action = new ListShipmentsAction($repo, new ShipmentListPayloadHelper());

    $out = $action->execute('purchased', 'USPS', 'q', 25);
    expect($out['meta'])->toHaveKey('per_page');
});

it('ListMyQueueAction passes user id and statuses', function () {
    $repo = mock(ShipmentRepo::class);

    $repo->shouldReceive('paginateAssignedTo')
        ->withArgs(fn ($userId, $statuses, $perPage) =>
            $userId === 7 && $statuses === ['purchased', 'packed'])
        ->andReturn(new LengthAwarePaginator([], 0, 25, 1));

    $action = new ListMyQueueAction($repo, new ShipmentListPayloadHelper());

    $user = new User();
    $user->id = 7;
    expect($action->execute($user))->toHaveKeys(['data', 'meta']);
});
