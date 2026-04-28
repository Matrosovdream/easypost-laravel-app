<?php

use App\Actions\Clients\SetClientFlexRateAction;
use App\Actions\Clients\UpdateClientAction;
use App\Helpers\Clients\ClientHelper;
use App\Models\Client;
use App\Repositories\Client\ClientRepo;
use Illuminate\Support\Facades\Gate;

function makeClientStub(?Client $c = null) {
    return new class($c) {
        public function __construct(private $c) {}
        public function newQuery() { return new class($this->c) {
            public function __construct(private $c) {}
            public function find($id) { return $this->c; }
        }; }
    };
}

it('UpdateClientAction aborts 404 when client missing', function () {
    $repo = mock(ClientRepo::class);
    $repo->shouldReceive('getModel')->andReturn(makeClientStub(null));

    $action = new UpdateClientAction($repo, new ClientHelper());

    expect(fn () => $action->execute(99, []))
        ->toThrow(\Symfony\Component\HttpKernel\Exception\HttpException::class);
});

it('UpdateClientAction calls repo updateAttributes', function () {
    $client = new Client(['company_name' => 'X', 'flexrate_markup_pct' => 0]);
    $client->id = 1;

    $repo = mock(ClientRepo::class);
    $repo->shouldReceive('getModel')->andReturn(makeClientStub($client));
    Gate::shouldReceive('authorize')->with('update', $client)->once();
    $repo->shouldReceive('updateAttributes')->andReturn($client);

    $action = new UpdateClientAction($repo, new ClientHelper());
    expect($action->execute(1, ['company_name' => 'New'])['id'])->toBe(1);
});

it('SetClientFlexRateAction aborts 404 when missing', function () {
    $repo = mock(ClientRepo::class);
    $repo->shouldReceive('getModel')->andReturn(makeClientStub(null));

    $action = new SetClientFlexRateAction($repo, new ClientHelper());

    expect(fn () => $action->execute(99, 10.0))
        ->toThrow(\Symfony\Component\HttpKernel\Exception\HttpException::class);
});

it('SetClientFlexRateAction calls repo setFlexRate', function () {
    $client = new Client(['company_name' => 'X', 'flexrate_markup_pct' => 0]);
    $client->id = 1;

    $repo = mock(ClientRepo::class);
    $repo->shouldReceive('getModel')->andReturn(makeClientStub($client));
    Gate::shouldReceive('authorize')->with('update', $client)->once();
    $repo->shouldReceive('setFlexRate')->with($client, 15.0, ['ground' => 12])
        ->andReturn($client);

    $action = new SetClientFlexRateAction($repo, new ClientHelper());
    expect($action->execute(1, 15.0, ['ground' => 12])['id'])->toBe(1);
});
