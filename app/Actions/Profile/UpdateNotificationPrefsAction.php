<?php

namespace App\Actions\Profile;

use App\Models\User;

class UpdateNotificationPrefsAction
{
    public function execute(User $user, array $prefs): array
    {
        // Persisted on users table (requires migration to add); for now accept & echo.
        return ['data' => $prefs];
    }
}
