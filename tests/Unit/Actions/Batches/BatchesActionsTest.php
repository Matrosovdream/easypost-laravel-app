<?php

use App\Actions\Batches\BuyBatchAction;
use App\Actions\Batches\GenerateBatchLabelsAction;
use App\Actions\Batches\ListBatchesAction;
use App\Actions\Batches\ShowBatchAction;
use App\Helpers\Batches\BatchHelper;
use App\Mixins\Integrations\EasyPost\EasyPostClient;
use App\Models\Batch;
use App\Models\User;
use App\Repositories\Operations\BatchRepo;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Gate;

beforeEach(function () {
    $this->repo = mock(BatchRepo::class);
    $this->ep = mock(EasyPostClient::class);
    $this->helper = new BatchHelper();
});

it('ListBatchesAction authorizes then paginates', function () {
    Gate::shouldReceive('authorize')->with('viewAny', Batch::class)->once();
    $this->repo->shouldReceive('paginateForTeam')->andReturn(new LengthAwarePaginator([], 0, 25, 1));

    $action = new ListBatchesAction($this->repo, $this->helper);
    $user = new User();
    $user->current_team_id = 3;

    expect($action->execute($user))->toHaveKeys(['data', 'meta']);
});

it('ShowBatchAction aborts 404 when missing', function () {
    $action = new ShowBatchAction($this->repo, $this->helper);
    $this->repo->shouldReceive('findWithShipments')->andReturn(null);

    expect(fn () => $action->execute(99))
        ->toThrow(\Symfony\Component\HttpKernel\Exception\HttpException::class);
});

it('BuyBatchAction marks purchased without ep_batch_id', function () {
    $action = new BuyBatchAction($this->ep, $this->repo, $this->helper);

    $batch = new Batch(['state' => 'created']);
    $batch->id = 1;
    $batch->ep_batch_id = null;

    $this->repo->shouldReceive('getModel')->andReturn(new class($batch) {
        public function __construct(private $b) {}
        public function newQuery() { return new class($this->b) {
            public function __construct(private $b) {}
            public function find($id) { return $this->b; }
        }; }
    });
    Gate::shouldReceive('authorize')->with('update', $batch)->once();
    $this->repo->shouldReceive('updateState')->with($batch, 'purchased')
        ->andReturnUsing(function ($b) { $b->state = 'purchased'; return $b; });

    $user = new User();
    expect($action->execute($user, 1))->toBe(['id' => 1, 'state' => 'purchased']);
});

it('GenerateBatchLabelsAction is a no-op when no ep_batch_id', function () {
    $action = new GenerateBatchLabelsAction($this->ep, $this->repo, $this->helper);

    $batch = new Batch();
    $batch->id = 1;
    $batch->ep_batch_id = null;
    $batch->label_pdf_s3_key = null;

    $this->repo->shouldReceive('getModel')->andReturn(new class($batch) {
        public function __construct(private $b) {}
        public function newQuery() { return new class($this->b) {
            public function __construct(private $b) {}
            public function find($id) { return $this->b; }
        }; }
    });
    Gate::shouldReceive('authorize')->with('update', $batch)->once();
    $this->ep->shouldNotReceive('labelBatch');

    expect($action->execute(1))->toBe(['id' => 1, 'label_url' => null]);
});
