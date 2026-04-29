<?php

use App\Actions\ScanForms\ListScanFormsAction;
use App\Actions\ScanForms\ShowScanFormAction;
use App\Helpers\ScanForms\ScanFormHelper;
use App\Models\ScanForm;
use App\Models\User;
use App\Repositories\Operations\ScanFormRepo;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Gate;

beforeEach(function () {
    $this->repo = mock(ScanFormRepo::class);
    $this->helper = new ScanFormHelper();
});

it('ListScanFormsAction returns paginated payload', function () {
    Gate::shouldReceive('authorize')->with('viewAny', ScanForm::class)->once();
    $this->repo->shouldReceive('paginateForTeam')->andReturn(new LengthAwarePaginator([], 0, 25, 1));

    $action = new ListScanFormsAction($this->repo, $this->helper);
    $user = new User();
    $user->current_team_id = 3;

    expect($action->execute($user))->toHaveKeys(['data', 'meta']);
});

it('ShowScanFormAction aborts 404 when missing', function () {
    $action = new ShowScanFormAction($this->repo, $this->helper);

    $this->repo->shouldReceive('getModel')->andReturn(new class {
        public function newQuery() { return new class {
            public function find($id) { return null; }
        }; }
    });

    expect(fn () => $action->execute(99))
        ->toThrow(\Symfony\Component\HttpKernel\Exception\HttpException::class);
});

it('ShowScanFormAction returns detail when authorized', function () {
    $action = new ShowScanFormAction($this->repo, $this->helper);

    $form = new ScanForm(['carrier' => 'USPS']);
    $form->id = 1;

    $this->repo->shouldReceive('getModel')->andReturn(new class($form) {
        public function __construct(private $f) {}
        public function newQuery() { return new class($this->f) {
            public function __construct(private $f) {}
            public function find($id) { return $this->f; }
        }; }
    });
    Gate::shouldReceive('authorize')->with('view', $form)->once();

    expect($action->execute(1)['carrier'])->toBe('USPS');
});
