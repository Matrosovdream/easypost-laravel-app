<?php

use App\Actions\Returns\DeclineReturnAction;
use App\Actions\Returns\ListReturnsAction;
use App\Actions\Returns\ShowReturnAction;
use App\Helpers\Returns\ReturnHelper;
use App\Models\ReturnRequest;
use App\Models\User;
use App\Repositories\Care\ReturnRequestRepo;
use App\Repositories\User\UserRepo;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Gate;

beforeEach(function () {
    $this->repo = mock(ReturnRequestRepo::class);
    $this->users = mock(UserRepo::class);
    $this->helper = new ReturnHelper();
});

it('ListReturnsAction calls authorize then paginateForTeam', function () {
    Gate::shouldReceive('authorize')->with('viewAny', ReturnRequest::class)->once();

    $action = new ListReturnsAction($this->repo, $this->users, $this->helper);

    $user = mock(User::class)->makePartial();
    $user->current_team_id = 3;
    $user->id = 1;
    $user->setRelation('roles', collect());
    $user->shouldReceive('rights')->andReturn(['returns.view.any']);

    $this->repo->shouldReceive('paginateForTeam')->andReturn(new LengthAwarePaginator([], 0, 25, 1));

    expect($action->execute($user))->toHaveKeys(['data', 'meta']);
});

it('ShowReturnAction aborts 404 when missing', function () {
    $action = new ShowReturnAction($this->repo, $this->helper);
    $this->repo->shouldReceive('findWithDetails')->andReturn(null);

    expect(fn () => $action->execute(99))
        ->toThrow(\Symfony\Component\HttpKernel\Exception\HttpException::class);
});

it('DeclineReturnAction aborts 403 without returns.approve', function () {
    $action = new DeclineReturnAction($this->repo, $this->helper);

    $return = new ReturnRequest(['status' => 'requested', 'notes' => null]);
    $return->id = 5;
    $this->repo->shouldReceive('findWithDetails')->andReturn($return);

    $user = mock(User::class)->makePartial();
    $user->shouldReceive('can')->andReturn(false);

    expect(fn () => $action->execute($user, 5, 'reason'))
        ->toThrow(\Symfony\Component\HttpKernel\Exception\HttpException::class);
});

it('DeclineReturnAction throws RuntimeException when not in requested status', function () {
    $action = new DeclineReturnAction($this->repo, $this->helper);

    $return = new ReturnRequest(['status' => 'approved', 'notes' => null]);
    $return->id = 5;
    $this->repo->shouldReceive('findWithDetails')->andReturn($return);

    $user = mock(User::class)->makePartial();
    $user->shouldReceive('can')->andReturn(true);

    expect(fn () => $action->execute($user, 5, null))
        ->toThrow(RuntimeException::class);
});

it('DeclineReturnAction marks declined and returns identity', function () {
    $action = new DeclineReturnAction($this->repo, $this->helper);

    $return = new ReturnRequest(['status' => 'requested', 'notes' => 'old']);
    $return->id = 5;
    $this->repo->shouldReceive('findWithDetails')->andReturn($return);

    $user = mock(User::class)->makePartial();
    $user->shouldReceive('can')->andReturn(true);
    $user->name = 'Stan';
    $user->id = 1;

    $declined = new ReturnRequest(['status' => 'declined']);
    $declined->id = 5;

    $this->repo->shouldReceive('markDeclined')->once()->andReturn($declined);

    expect($action->execute($user, 5, 'why'))->toBe(['id' => 5, 'status' => 'declined']);
});
