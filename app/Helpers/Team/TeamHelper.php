<?php

namespace App\Helpers\Team;

use App\Models\Team;

class TeamHelper
{
    public function toDetail(Team $team): array
    {
        return [
            'id' => $team->id,
            'name' => $team->name,
            'plan' => $team->plan,
            'mode' => $team->mode,
            'status' => $team->status,
            'time_zone' => $team->time_zone,
            'default_currency' => $team->default_currency,
            'settings' => $team->settings,
            'logo_s3_key' => $team->logo_s3_key,
        ];
    }
}
