<?php

namespace App\Http\Controllers\Api\Settings;

use App\Http\Controllers\Controller;
use App\Repositories\Team\TeamRepo;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class TeamController extends Controller
{
    public function __construct(private readonly TeamRepo $teams) {}

    public function show(Request $request): JsonResponse
    {
        abort_unless(in_array('settings.team.edit', $request->user()->rights(), true)
            || in_array('users.manage', $request->user()->rights(), true), 403);

        $mapped = $this->teams->getByID($request->user()->current_team_id);
        abort_if(! $mapped, 404);
        $team = $mapped['Model'];

        return response()->json([
            'id' => $team->id,
            'name' => $team->name,
            'plan' => $team->plan,
            'mode' => $team->mode,
            'status' => $team->status,
            'time_zone' => $team->time_zone,
            'default_currency' => $team->default_currency,
            'settings' => $team->settings,
            'logo_s3_key' => $team->logo_s3_key,
        ]);
    }

    public function update(Request $request): JsonResponse
    {
        abort_unless(in_array('settings.team.edit', $request->user()->rights(), true), 403);

        $v = $request->validate([
            'name' => ['sometimes', 'string', 'max:120'],
            'time_zone' => ['sometimes', 'string', 'max:64'],
            'default_currency' => ['sometimes', 'string', 'size:3'],
            'settings' => ['sometimes', 'array'],
            'logo_s3_key' => ['sometimes', 'nullable', 'string', 'max:255'],
        ]);

        $this->teams->update((int) $request->user()->current_team_id, $v);
        return response()->json(['ok' => true]);
    }
}
