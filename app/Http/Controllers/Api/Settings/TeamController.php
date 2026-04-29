<?php

namespace App\Http\Controllers\Api\Settings;

use App\Actions\Team\ShowTeamAction;
use App\Actions\Team\UpdateTeamAction;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\Settings\UpdateTeamRequest;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class TeamController extends Controller
{
    public function __construct(
        private readonly ShowTeamAction $show,
        private readonly UpdateTeamAction $update,
    ) {}

    public function show(Request $request): JsonResponse
    {
        return response()->json($this->show->execute($request->user()));
    }

    public function update(UpdateTeamRequest $request): JsonResponse
    {
        return response()->json($this->update->execute($request->user(), $request->validated()));
    }
}
