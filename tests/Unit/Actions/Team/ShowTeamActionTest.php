<?php

use App\Actions\Team\ShowTeamAction;
use App\Helpers\Team\TeamHelper;
use App\Models\Team;
use App\Models\User;
use App\Repositories\Team\TeamRepo;

beforeEach(function () {
    $this->teams = mock(TeamRepo::class);
    $this->action = new ShowTeamAction($this->teams, new TeamHelper());
});

it('aborts 403 when user lacks settings.team.edit and users.manage', function () {
    $user = mock(User::class)->makePartial();
    $user->shouldReceive('rights')->andReturn([]);

    expect(fn () => $this->action->execute($user))
        ->toThrow(\Symfony\Component\HttpKernel\Exception\HttpException::class);
});

it('aborts 404 when team not found', function () {
    $user = mock(User::class)->makePartial();
    $user->shouldReceive('rights')->andReturn(['settings.team.edit']);
    $user->current_team_id = 1;

    $this->teams->shouldReceive('getByID')->andReturn(null);

    expect(fn () => $this->action->execute($user))
        ->toThrow(\Symfony\Component\HttpKernel\Exception\HttpException::class);
});

it('returns team detail when authorized', function () {
    $user = mock(User::class)->makePartial();
    $user->shouldReceive('rights')->andReturn(['users.manage']);
    $user->current_team_id = 1;

    $team = new Team(['name' => 'Acme', 'plan' => 'team']);
    $team->id = 1;

    $this->teams->shouldReceive('getByID')->andReturn(['Model' => $team]);

    $out = $this->action->execute($user);
    expect($out['name'])->toBe('Acme');
});
