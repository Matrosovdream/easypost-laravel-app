<?php

namespace App\Helpers\Profile;

use App\Models\User;

class NotificationPrefsHelper
{
    public const DEFAULTS = [
        'email.shipment.delivered' => true,
        'email.return.status' => true,
        'email.claim.status' => true,
        'email.approval.requested' => true,
    ];

    public function forUser(User $user): array
    {
        return $user->notification_prefs ?? self::DEFAULTS;
    }
}
