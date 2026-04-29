<?php

use App\Actions\Profile\UpdateProfileAction;
use App\Models\User;
use App\Repositories\User\UserRepo;

it('strips null values and forwards to repo->update', function () {
    $repo = mock(UserRepo::class);
    $repo->shouldReceive('update')
        ->withArgs(fn ($id, $patch) => $id === 7 && $patch === ['name' => 'Stan'])
        ->once();

    $action = new UpdateProfileAction($repo);

    $user = new User();
    $user->id = 7;

    expect($action->execute($user, ['name' => 'Stan', 'phone' => null]))->toBe(['ok' => true]);
});
