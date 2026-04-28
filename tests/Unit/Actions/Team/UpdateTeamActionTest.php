<?php

use App\Actions\Team\UpdateTeamAction;
use App\Models\User;
use App\Repositories\Team\TeamRepo;

beforeEach(function () {
    $this->teams = mock(TeamRepo::class);
    $this->action = new UpdateTeamAction($this->teams);
});

it('aborts 403 when user lacks settings.team.edit', function () {
    $user = mock(User::class)->makePartial();
    $user->shouldReceive('rights')->andReturn([]);

    expect(fn () => $this->action->execute($user, ['name' => 'X']))
        ->toThrow(\Symfony\Component\HttpKernel\Exception\HttpException::class);
});

it('updates the user current team and returns ok', function () {
    $user = mock(User::class)->makePartial();
    $user->shouldReceive('rights')->andReturn(['settings.team.edit']);
    $user->current_team_id = 1;

    $this->teams->shouldReceive('update')
        ->with(1, ['name' => 'New'])
        ->once();

    expect($this->action->execute($user, ['name' => 'New']))->toBe(['ok' => true]);
});
