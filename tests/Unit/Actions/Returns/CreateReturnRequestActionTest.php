<?php

use App\Actions\Returns\CreateReturnRequestAction;
use App\Helpers\Returns\ReturnHelper;
use App\Models\ReturnRequest;
use App\Models\Shipment;
use App\Models\User;
use App\Repositories\Care\ReturnRequestRepo;
use App\Repositories\Shipping\ShipmentRepo;

beforeEach(function () {
    $this->returns = mock(ReturnRequestRepo::class);
    $this->shipments = mock(ShipmentRepo::class);
    $this->action = new CreateReturnRequestAction($this->returns, $this->shipments, new ReturnHelper());
});

it('throws when original shipment is in another team', function () {
    $orig = new Shipment();
    $orig->team_id = 99;
    $this->shipments->shouldReceive('findUnscoped')->andReturn($orig);

    $user = new User();
    $user->current_team_id = 3;

    expect(fn () => $this->action->execute($user, ['original_shipment_id' => 1]))
        ->toThrow(RuntimeException::class, 'Original shipment not found');
});

it('creates return and returns identity', function () {
    $orig = new Shipment();
    $orig->team_id = 3;
    $orig->id = 1;
    $orig->client_id = 7;

    $this->shipments->shouldReceive('findUnscoped')->andReturn($orig);

    $return = new ReturnRequest(['status' => 'requested']);
    $return->id = 5;

    $this->returns->shouldReceive('create')->andReturn(['Model' => $return]);

    $user = new User();
    $user->id = 7;
    $user->current_team_id = 3;

    expect($this->action->execute($user, ['original_shipment_id' => 1]))
        ->toBe(['id' => 5, 'status' => 'requested']);
});
