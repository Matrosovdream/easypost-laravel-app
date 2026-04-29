<?php

use App\Actions\Users\ListTeamUsersAction;
use App\Helpers\Users\UserManagementHelper;
use App\Models\User;
use App\Repositories\User\UserRepo;

beforeEach(function () {
    $this->users = mock(UserRepo::class);
    $this->action = new ListTeamUsersAction($this->users, new UserManagementHelper());
});

it('aborts 403 when user lacks users.manage', function () {
    $user = mock(User::class)->makePartial();
    $user->shouldReceive('rights')->andReturn([]);

    expect(fn () => $this->action->execute($user))
        ->toThrow(\Symfony\Component\HttpKernel\Exception\HttpException::class);
});

it('returns shaped list when authorized', function () {
    $user = mock(User::class)->makePartial();
    $user->shouldReceive('rights')->andReturn(['users.manage']);
    $user->current_team_id = 3;

    $this->users->shouldReceive('listTeamMembers')->with(3)->andReturn(collect([
        (object) [
            'id' => 1, 'name' => 'Stan', 'email' => 's@x', 'role_slug' => 'admin', 'role_name' => 'Admin',
            'is_active' => 1, 'last_login_at' => null,
            'spending_cap_cents' => null, 'daily_cap_cents' => null,
            'client_id' => null, 'membership_status' => 'active',
        ],
    ]));

    $out = $this->action->execute($user);
    expect($out['data']->count())->toBe(1);
    expect($out['data'][0]['is_active'])->toBeTrue();
});
