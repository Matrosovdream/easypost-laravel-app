<?php

use App\Actions\Addresses\CreateAndVerifyAddressAction;
use App\Actions\Addresses\VerifyExistingAddressAction;
use App\Helpers\Addresses\AddressHelper;
use App\Mixins\Integrations\EasyPost\EasyPostClient;
use App\Models\Address;
use App\Models\User;
use App\Repositories\Address\AddressRepo;
use Illuminate\Support\Facades\Gate;

beforeEach(function () {
    $this->ep = mock(EasyPostClient::class);
    $this->repo = mock(AddressRepo::class);
    $this->helper = new AddressHelper();
});

it('creates address locally when verify=false (no EP call)', function () {
    $action = new CreateAndVerifyAddressAction($this->ep, $this->repo, $this->helper);

    $address = new Address(['street1' => '1 Main', 'country' => 'US']);
    $address->id = 1;

    $this->ep->shouldNotReceive('createAndVerifyAddress');
    $this->repo->shouldReceive('createForTeam')->andReturn($address);

    $user = new User();
    $user->current_team_id = 3;

    expect($action->execute($user, ['street1' => '1 Main', 'country' => 'US'], verify: false)['id'])
        ->toBe(1);
});

it('falls through when EP throws and creates locally unverified', function () {
    $action = new CreateAndVerifyAddressAction($this->ep, $this->repo, $this->helper);

    $address = new Address(['street1' => '1 Main', 'country' => 'US']);
    $address->id = 1;

    $this->ep->shouldReceive('createAndVerifyAddress')->andThrow(new \RuntimeException('down'));
    $this->repo->shouldReceive('createForTeam')
        ->withArgs(fn ($teamId, $data) => $data['verified'] === false)
        ->andReturn($address);

    $user = new User();
    $user->current_team_id = 3;

    expect($action->execute($user, ['street1' => '1 Main', 'country' => 'US']))->toHaveKey('id');
});

it('VerifyExistingAddressAction is a no-op when ep_address_id is null', function () {
    $action = new VerifyExistingAddressAction($this->ep, $this->repo, $this->helper);

    $address = new Address();
    $address->id = 1;
    $address->ep_address_id = null;

    $this->repo->shouldReceive('getModel')->andReturn(new class($address) {
        public function __construct(private $a) {}
        public function newQuery() { return new class($this->a) {
            public function __construct(private $a) {}
            public function find($id) { return $this->a; }
        }; }
    });
    Gate::shouldReceive('authorize')->with('verify', $address)->once();
    $this->ep->shouldNotReceive('verifyAddress');

    expect($action->execute(1)['id'])->toBe(1);
});
