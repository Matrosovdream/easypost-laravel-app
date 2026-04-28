<?php

namespace App\Actions\Users;

use App\Models\User;
use App\Repositories\User\UserRepo;

class SetUserActiveAction
{
    public function __construct(
        private readonly UserRepo $users,
    ) {}

    public function execute(User $actor, int $targetId, bool $active): array
    {
        abort_unless(in_array('users.manage', $actor->rights(), true), 403);

        if (! $active && $targetId === (int) $actor->id) {
            abort(422, 'Cannot disable yourself.');
        }

        $target = $this->users->getModel()->newQuery()->find($targetId);
        abort_if(! $target, 404);

        $this->users->setActive($target->id, $active);

        return ['ok' => true];
    }
}
