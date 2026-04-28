<?php

namespace App\Actions\Profile;

use App\Models\User;
use App\Repositories\User\UserRepo;

class UpdateProfileAction
{
    public function __construct(private readonly UserRepo $users) {}

    public function execute(User $user, array $input): array
    {
        $patch = array_filter([
            'name' => $input['name'] ?? null,
            'phone' => $input['phone'] ?? null,
            'locale' => $input['locale'] ?? null,
            'timezone' => $input['timezone'] ?? null,
        ], fn ($v) => $v !== null);

        $this->users->update($user->id, $patch);

        return ['ok' => true];
    }
}
