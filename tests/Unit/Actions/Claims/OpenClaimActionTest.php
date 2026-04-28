<?php

use App\Actions\Claims\OpenClaimAction;
use App\Helpers\Claims\ClaimHelper;
use App\Models\Claim;
use App\Models\Shipment;
use App\Models\User;
use App\Repositories\Care\ClaimRepo;
use App\Repositories\Shipping\ShipmentRepo;

beforeEach(function () {
    $this->claims = mock(ClaimRepo::class);
    $this->shipments = mock(ShipmentRepo::class);
    $this->action = new OpenClaimAction($this->claims, $this->shipments, new ClaimHelper());
});

it('throws when shipment not in team', function () {
    $shipment = new Shipment();
    $shipment->team_id = 999;

    $this->shipments->shouldReceive('findUnscoped')->andReturn($shipment);

    $user = new User();
    $user->current_team_id = 3;

    expect(fn () => $this->action->execute($user, [
        'shipment_id' => 1, 'amount_cents' => 100, 'description' => 'd',
    ]))->toThrow(RuntimeException::class, 'Shipment not found');
});

it('throws on unsupported claim type', function () {
    $shipment = new Shipment();
    $shipment->team_id = 3;
    $shipment->id = 1;

    $this->shipments->shouldReceive('findUnscoped')->andReturn($shipment);

    $user = new User();
    $user->current_team_id = 3;

    expect(fn () => $this->action->execute($user, [
        'shipment_id' => 1, 'type' => 'bogus', 'amount_cents' => 100, 'description' => 'd',
    ]))->toThrow(RuntimeException::class, 'Unsupported claim type');
});

it('creates open claim and returns identity', function () {
    $shipment = new Shipment();
    $shipment->team_id = 3;
    $shipment->id = 1;

    $this->shipments->shouldReceive('findUnscoped')->andReturn($shipment);

    $claim = new Claim(['state' => 'open']);
    $claim->id = 5;
    $this->claims->shouldReceive('create')->andReturn(['Model' => $claim]);

    $user = new User();
    $user->id = 7;
    $user->current_team_id = 3;

    $out = $this->action->execute($user, [
        'shipment_id' => 1, 'type' => 'damage', 'amount_cents' => 100, 'description' => 'd',
    ]);
    expect($out)->toBe(['id' => 5, 'state' => 'open']);
});
