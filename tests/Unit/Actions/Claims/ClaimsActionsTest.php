<?php

use App\Actions\Claims\ApproveClaimAction;
use App\Actions\Claims\CloseClaimAction;
use App\Actions\Claims\ListClaimsAction;
use App\Actions\Claims\MarkClaimPaidAction;
use App\Actions\Claims\ShowClaimAction;
use App\Helpers\Claims\ClaimHelper;
use App\Models\Claim;
use App\Models\User;
use App\Repositories\Care\ClaimRepo;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Gate;

beforeEach(function () {
    $this->repo = mock(ClaimRepo::class);
    $this->helper = new ClaimHelper();
});

it('ListClaimsAction returns paginated payload', function () {
    Gate::shouldReceive('authorize')->with('viewAny', Claim::class)->once();
    $this->repo->shouldReceive('paginateForTeam')->andReturn(new LengthAwarePaginator([], 0, 25, 1));

    $action = new ListClaimsAction($this->repo, $this->helper);
    $user = new User();
    $user->current_team_id = 3;

    expect($action->execute($user))->toHaveKeys(['data', 'meta']);
});

it('ShowClaimAction aborts 404 when missing', function () {
    $action = new ShowClaimAction($this->repo, $this->helper);
    $this->repo->shouldReceive('findInTeam')->andReturn(null);

    $user = new User();
    $user->current_team_id = 3;

    expect(fn () => $action->execute($user, 99))
        ->toThrow(\Symfony\Component\HttpKernel\Exception\HttpException::class);
});

it('ApproveClaimAction throws on bad state', function () {
    $action = new ApproveClaimAction($this->repo, $this->helper);

    $claim = new Claim(['state' => 'paid']);
    $claim->id = 5;
    $this->repo->shouldReceive('findInTeam')->andReturn($claim);

    $user = mock(User::class)->makePartial();
    $user->shouldReceive('can')->andReturn(true);
    $user->current_team_id = 3;

    expect(fn () => $action->execute($user, 5))->toThrow(RuntimeException::class);
});

it('ApproveClaimAction transitions to approved with default recovered=amount', function () {
    $action = new ApproveClaimAction($this->repo, $this->helper);

    $claim = new Claim(['state' => 'submitted', 'amount_cents' => 1000]);
    $claim->id = 5;
    $this->repo->shouldReceive('findInTeam')->andReturn($claim);

    $user = mock(User::class)->makePartial();
    $user->shouldReceive('can')->andReturn(true);
    $user->id = 1;
    $user->current_team_id = 3;

    $approved = new Claim(['state' => 'approved', 'recovered_cents' => 1000]);
    $approved->id = 5;

    $this->repo->shouldReceive('transition')
        ->withArgs(fn ($c, $data, $event) => $data['recovered_cents'] === 1000 && $data['state'] === 'approved')
        ->once()
        ->andReturn($approved);

    expect($action->execute($user, 5))
        ->toBe(['id' => 5, 'state' => 'approved', 'recovered_cents' => 1000]);
});

it('MarkClaimPaidAction transitions and uses fallback recovered_cents', function () {
    $action = new MarkClaimPaidAction($this->repo, $this->helper);

    $claim = new Claim(['state' => 'approved', 'amount_cents' => 500, 'recovered_cents' => 400]);
    $claim->id = 5;
    $this->repo->shouldReceive('findInTeam')->andReturn($claim);

    $user = mock(User::class)->makePartial();
    $user->shouldReceive('can')->andReturn(true);
    $user->id = 1;
    $user->current_team_id = 3;

    $paid = new Claim(['state' => 'paid']);
    $paid->id = 5;

    $this->repo->shouldReceive('transition')
        ->withArgs(fn ($c, $data) => $data['state'] === 'paid' && $data['recovered_cents'] === 400)
        ->once()
        ->andReturn($paid);

    expect($action->execute($user, 5))->toBe(['id' => 5, 'state' => 'paid']);
});

it('CloseClaimAction transitions to closed with reason', function () {
    $action = new CloseClaimAction($this->repo, $this->helper);

    $claim = new Claim(['state' => 'paid']);
    $claim->id = 5;
    $this->repo->shouldReceive('findInTeam')->andReturn($claim);
    Gate::shouldReceive('authorize')->with('view', $claim)->once();

    $user = new User();
    $user->id = 1;
    $user->current_team_id = 3;

    $closed = new Claim(['state' => 'closed']);
    $closed->id = 5;

    $this->repo->shouldReceive('transition')
        ->withArgs(fn ($c, $data, $event) => $data['close_reason'] === 'why' && $event['reason'] === 'why')
        ->once()
        ->andReturn($closed);

    expect($action->execute($user, 5, 'why'))->toBe(['id' => 5, 'state' => 'closed']);
});
