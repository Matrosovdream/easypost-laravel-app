<?php

use App\Actions\Settings\ListAuditLogsAction;
use App\Helpers\Settings\AuditLogHelper;
use App\Models\User;
use App\Repositories\Infra\AuditLogRepo;
use Illuminate\Pagination\LengthAwarePaginator;

beforeEach(function () {
    $this->repo = mock(AuditLogRepo::class);
    $this->action = new ListAuditLogsAction($this->repo, new AuditLogHelper());
});

it('aborts 403 when user has no audit_log right', function () {
    $user = mock(User::class)->makePartial();
    $user->shouldReceive('rights')->andReturn([]);

    expect(fn () => $this->action->execute($user))
        ->toThrow(\Symfony\Component\HttpKernel\Exception\HttpException::class);
});

it('passes null userIdScope when user has audit_log.view.any', function () {
    $user = mock(User::class)->makePartial();
    $user->shouldReceive('rights')->andReturn(['audit_log.view.any']);
    $user->current_team_id = 3;
    $user->id = 7;

    $this->repo->shouldReceive('paginateForTeam')
        ->withArgs(fn ($teamId, $userIdScope) => $teamId === 3 && $userIdScope === null)
        ->andReturn(new LengthAwarePaginator([], 0, 50, 1));

    $this->action->execute($user);
});

it('passes user id as scope when user only has audit_log.view.own', function () {
    $user = mock(User::class)->makePartial();
    $user->shouldReceive('rights')->andReturn(['audit_log.view.own']);
    $user->current_team_id = 3;
    $user->id = 7;

    $this->repo->shouldReceive('paginateForTeam')
        ->withArgs(fn ($teamId, $userIdScope) => $userIdScope === 7)
        ->andReturn(new LengthAwarePaginator([], 0, 50, 1));

    $this->action->execute($user);
});
