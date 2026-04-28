<?php

use App\Actions\Clients\ShowClientAction;
use App\Helpers\Clients\ClientHelper;
use App\Models\Client;
use App\Repositories\Client\ClientRepo;
use Illuminate\Support\Facades\Gate;

it('aborts 404 when client missing', function () {
    $repo = mock(ClientRepo::class);
    $repo->shouldReceive('getModel')->andReturn(new class {
        public function newQuery() { return new class {
            public function find($id) { return null; }
        }; }
    });

    $action = new ShowClientAction($repo, new ClientHelper());

    expect(fn () => $action->execute(99))
        ->toThrow(\Symfony\Component\HttpKernel\Exception\HttpException::class);
});

it('returns shaped detail when authorized', function () {
    $client = new Client(['company_name' => 'Acme', 'flexrate_markup_pct' => 0]);
    $client->id = 1;

    $repo = mock(ClientRepo::class);
    $repo->shouldReceive('getModel')->andReturn(new class($client) {
        public function __construct(private $c) {}
        public function newQuery() { return new class($this->c) {
            public function __construct(private $c) {}
            public function find($id) { return $this->c; }
        }; }
    });
    Gate::shouldReceive('authorize')->with('view', $client)->once();

    $action = new ShowClientAction($repo, new ClientHelper());
    expect($action->execute(1)['company_name'])->toBe('Acme');
});
