<?php

namespace App\Http\Controllers\Api\Settings;

use App\Actions\Users\ListPeopleByRoleAction;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PeopleController extends Controller
{
    public function __construct(
        private readonly ListPeopleByRoleAction $list,
    ) {}

    public function index(Request $request, string $role): JsonResponse
    {
        return response()->json($this->list->execute($request->user(), $role));
    }
}
