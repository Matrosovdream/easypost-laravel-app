<?php

use App\Actions\Shipments\ListApprovalsAction;
use App\Helpers\Shipments\ApprovalHelper;
use App\Models\User;
use App\Repositories\Shipping\ApprovalRepo;
use Illuminate\Pagination\LengthAwarePaginator;

beforeEach(function () {
    $this->repo = mock(ApprovalRepo::class);
    $this->helper = new ApprovalHelper();
});

it('aborts 403 when user cannot approve shipments', function () {
    $action = new ListApprovalsAction($this->repo, $this->helper);

    $user = mock(User::class)->makePartial();
    $user->shouldReceive('can')->andReturn(false);

    expect(fn () => $action->execute($user))
        ->toThrow(\Symfony\Component\HttpKernel\Exception\HttpException::class);
});

it('returns shaped payload when authorized', function () {
    $action = new ListApprovalsAction($this->repo, $this->helper);

    $user = mock(User::class)->makePartial();
    $user->shouldReceive('can')->andReturn(true);
    $user->current_team_id = 3;

    $this->repo->shouldReceive('paginateForTeam')->andReturn(new LengthAwarePaginator([], 0, 25, 1));

    expect($action->execute($user))->toHaveKeys(['data', 'meta']);
});
