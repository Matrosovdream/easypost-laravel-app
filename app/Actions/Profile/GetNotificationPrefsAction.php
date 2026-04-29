<?php

namespace App\Actions\Profile;

use App\Helpers\Profile\NotificationPrefsHelper;
use App\Models\User;

class GetNotificationPrefsAction
{
    public function __construct(
        private readonly NotificationPrefsHelper $helper,
    ) {}

    public function execute(User $user): array
    {
        return ['data' => $this->helper->forUser($user)];
    }
}
