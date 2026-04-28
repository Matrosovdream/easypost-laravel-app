<?php

use App\Actions\Addresses\DeleteAddressAction;
use App\Actions\Addresses\ListAddressesAction;
use App\Actions\Addresses\ShowAddressAction;
use App\Actions\Addresses\UpdateAddressAction;
use App\Helpers\Addresses\AddressHelper;
use App\Models\Address;
use App\Models\User;
use App\Policies\AddressPolicy;
use App\Repositories\Address\AddressRepo;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Gate;

beforeEach(function () {
    $this->repo = mock(AddressRepo::class);
    $this->helper = new AddressHelper();
    Gate::policy(Address::class, AddressPolicy::class);
});

function fakeAddressModelStub(?Address $address = null)
{
    return new class($address) {
        public function __construct(private ?Address $address) {}
        public function newQuery() {
            $a = $this->address;
            return new class($a) {
                public function __construct(private ?Address $a) {}
                public function find($id) { return $this->a; }
            };
        }
    };
}

it('ListAddressesAction returns paginated payload', function () {
    Gate::shouldReceive('authorize')->with('viewAny', Address::class)->once();

    $action = new ListAddressesAction($this->repo, $this->helper);

    $user = new User();
    $user->current_team_id = 3;

    $this->repo->shouldReceive('paginateForTeam')
        ->andReturn(new LengthAwarePaginator([], 0, 25, 1));

    expect($action->execute($user))->toHaveKeys(['data', 'meta']);
});

it('ShowAddressAction aborts 404 when not found', function () {
    $action = new ShowAddressAction($this->repo, $this->helper);
    $this->repo->shouldReceive('getModel')->andReturn(fakeAddressModelStub(null));

    expect(fn () => $action->execute(99))
        ->toThrow(\Symfony\Component\HttpKernel\Exception\HttpException::class);
});

it('UpdateAddressAction calls repo updateAttributes and returns shape', function () {
    $action = new UpdateAddressAction($this->repo, $this->helper);

    $address = new Address(['name' => 'Stan', 'street1' => '1 Main', 'country' => 'US']);
    $address->id = 1;

    $this->repo->shouldReceive('getModel')->andReturn(fakeAddressModelStub($address));
    Gate::shouldReceive('authorize')->with('view', $address)->once();
    $this->repo->shouldReceive('updateAttributes')->andReturn($address);

    $out = $action->execute(1, ['name' => 'New']);
    expect($out['id'])->toBe(1);
});

it('DeleteAddressAction calls repo deleteRow and returns ok', function () {
    $action = new DeleteAddressAction($this->repo);

    $address = new Address();
    $address->id = 1;

    $this->repo->shouldReceive('getModel')->andReturn(fakeAddressModelStub($address));
    Gate::shouldReceive('authorize')->with('delete', $address)->once();
    $this->repo->shouldReceive('deleteRow')->with($address)->once();

    expect($action->execute(1))->toBe(['ok' => true]);
});
