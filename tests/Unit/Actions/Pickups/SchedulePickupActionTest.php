<?php

use App\Actions\Pickups\SchedulePickupAction;
use App\Helpers\Pickups\PickupHelper;
use App\Mixins\Integrations\EasyPost\EasyPostClient;
use App\Models\Address;
use App\Models\Pickup;
use App\Models\User;
use App\Repositories\Address\AddressRepo;
use App\Repositories\Operations\PickupRepo;
use Illuminate\Support\Facades\DB;

beforeEach(function () {
    $this->ep = mock(EasyPostClient::class);
    $this->pickups = mock(PickupRepo::class);
    $this->addresses = mock(AddressRepo::class);
    $this->action = new SchedulePickupAction($this->ep, $this->pickups, $this->addresses, new PickupHelper());
});

it('throws when address is not in team', function () {
    $this->addresses->shouldReceive('findInTeam')->andReturn(null);

    $user = new User();
    $user->current_team_id = 3;

    expect(fn () => $this->action->execute($user, [
        'address_id' => 1, 'min_datetime' => '2026-01-01', 'max_datetime' => '2026-01-02',
    ]))->toThrow(RuntimeException::class, 'Address not found');
});

it('schedules pickup and returns shaped payload', function () {
    $address = new Address(['ep_address_id' => 'adr_x']);
    $address->id = 1;
    $this->addresses->shouldReceive('findInTeam')->andReturn($address);

    $pickup = new Pickup(['status' => 'unknown', 'rates_snapshot' => [['rate' => '5.00']]]);
    $pickup->id = 5;

    DB::shouldReceive('transaction')->andReturnUsing(fn ($cb) => $cb());
    $this->ep->shouldReceive('createPickup')->andReturn(['id' => 'pick_x', 'pickup_rates' => [['rate' => '5.00']]]);
    $this->pickups->shouldReceive('create')->andReturn(['Model' => $pickup]);

    $user = new User();
    $user->id = 7;
    $user->current_team_id = 3;

    $out = $this->action->execute($user, [
        'address_id' => 1, 'min_datetime' => '2026-01-01', 'max_datetime' => '2026-01-02',
    ]);
    expect($out)->toBe(['id' => 5, 'status' => 'unknown', 'rates' => [['rate' => '5.00']]]);
});
