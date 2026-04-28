<?php

use App\Actions\Users\InviteUserAction;
use App\Actions\Users\ChangeUserRoleAction;
use App\Models\User;
use App\Repositories\Infra\InvitationRepo;
use App\Repositories\User\RoleRepo;
use App\Repositories\User\UserRepo;

it('InviteUserAction aborts 403 without users.invite right', function () {
    $action = new InviteUserAction(mock(RoleRepo::class), mock(InvitationRepo::class));

    $user = mock(User::class)->makePartial();
    $user->shouldReceive('rights')->andReturn([]);

    expect(fn () => $action->execute($user, ['email' => 'x@x', 'role_slug' => 'shipper']))
        ->toThrow(\Symfony\Component\HttpKernel\Exception\HttpException::class);
});

it('InviteUserAction aborts 403 when inviting admin without admin right', function () {
    $action = new InviteUserAction(mock(RoleRepo::class), mock(InvitationRepo::class));

    $user = mock(User::class)->makePartial();
    $user->shouldReceive('rights')->andReturn(['users.invite']);

    expect(fn () => $action->execute($user, ['email' => 'x@x', 'role_slug' => 'admin']))
        ->toThrow(\Symfony\Component\HttpKernel\Exception\HttpException::class);
});

it('InviteUserAction creates invitation and returns id+token+expiry', function () {
    $roles = mock(RoleRepo::class);
    $invites = mock(InvitationRepo::class);
    $action = new InviteUserAction($roles, $invites);

    $inviter = mock(User::class)->makePartial();
    $inviter->shouldReceive('rights')->andReturn(['users.invite']);
    $inviter->id = 7;
    $inviter->current_team_id = 3;

    $roles->shouldReceive('getBySlug')->with('shipper')->andReturn(['id' => 5]);
    $invites->shouldReceive('create')->andReturn(99);

    $out = $action->execute($inviter, ['email' => 'X@X.test', 'role_slug' => 'shipper']);
    expect($out)->toHaveKeys(['id', 'token', 'expires_at']);
    expect($out['id'])->toBe(99);
});

it('ChangeUserRoleAction aborts 403 without users.role.assign right', function () {
    $action = new ChangeUserRoleAction(mock(RoleRepo::class), mock(UserRepo::class));

    $actor = mock(User::class)->makePartial();
    $actor->shouldReceive('rights')->andReturn([]);

    expect(fn () => $action->execute($actor, 1, 'shipper'))
        ->toThrow(\Symfony\Component\HttpKernel\Exception\HttpException::class);
});

it('ChangeUserRoleAction aborts 403 when promoting to admin without admin right', function () {
    $action = new ChangeUserRoleAction(mock(RoleRepo::class), mock(UserRepo::class));

    $actor = mock(User::class)->makePartial();
    $actor->shouldReceive('rights')->andReturn(['users.role.assign']);

    expect(fn () => $action->execute($actor, 1, 'admin'))
        ->toThrow(\Symfony\Component\HttpKernel\Exception\HttpException::class);
});
