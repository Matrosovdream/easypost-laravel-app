<?php

use App\Actions\Pickups\BuyPickupAction;
use App\Actions\Pickups\CancelPickupAction;
use App\Actions\Pickups\ListPickupsAction;
use App\Actions\Pickups\ShowPickupAction;
use App\Helpers\Pickups\PickupHelper;
use App\Mixins\Integrations\EasyPost\EasyPostClient;
use App\Models\Pickup;
use App\Models\User;
use App\Repositories\Operations\PickupRepo;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Gate;

beforeEach(function () {
    $this->repo = mock(PickupRepo::class);
    $this->ep = mock(EasyPostClient::class);
    $this->helper = new PickupHelper();
});

it('ListPickupsAction returns paginated payload', function () {
    Gate::shouldReceive('authorize')->with('viewAny', Pickup::class)->once();

    $action = new ListPickupsAction($this->repo, $this->helper);
    $user = new User();
    $user->current_team_id = 3;

    $this->repo->shouldReceive('paginateForTeam')->andReturn(new LengthAwarePaginator([], 0, 25, 1));

    expect($action->execute($user))->toHaveKeys(['data', 'meta']);
});

it('ShowPickupAction aborts 404 when missing', function () {
    $action = new ShowPickupAction($this->repo, $this->helper);
    $this->repo->shouldReceive('findWithAddress')->andReturn(null);

    expect(fn () => $action->execute(99))
        ->toThrow(\Symfony\Component\HttpKernel\Exception\HttpException::class);
});

it('CancelPickupAction marks cancelled when authorized', function () {
    $action = new CancelPickupAction($this->ep, $this->repo, $this->helper);

    $pickup = new Pickup();
    $pickup->id = 1;
    $pickup->ep_pickup_id = null;

    $this->repo->shouldReceive('findWithAddress')->with(1)->andReturn($pickup);
    Gate::shouldReceive('authorize')->with('cancel', $pickup)->once();
    $this->repo->shouldReceive('markCancelled')->with($pickup)
        ->andReturnUsing(function ($p) { $p->status = 'cancelled'; return $p; });

    $user = new User();
    expect($action->execute($user, 1))->toBe(['id' => 1, 'status' => 'cancelled']);
});

it('BuyPickupAction succeeds without ep_pickup_id', function () {
    $action = new BuyPickupAction($this->ep, $this->repo, $this->helper);

    $pickup = new Pickup();
    $pickup->id = 1;
    $pickup->ep_pickup_id = null;

    $this->repo->shouldReceive('findWithAddress')->with(1)->andReturn($pickup);
    Gate::shouldReceive('authorize')->with('view', $pickup)->once();
    $this->repo->shouldReceive('markScheduled')->with($pickup, ['carrier' => 'USPS', 'service' => 'Priority'])
        ->andReturnUsing(function ($p) { $p->status = 'scheduled'; $p->confirmation = null; return $p; });

    $user = new User();
    expect($action->execute($user, 1, 'USPS', 'Priority')['status'])->toBe('scheduled');
});
