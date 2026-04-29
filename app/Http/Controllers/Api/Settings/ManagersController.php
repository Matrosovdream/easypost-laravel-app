<?php

namespace App\Http\Controllers\Api\Settings;

use App\Actions\Users\ListManagersAction;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ManagersController extends Controller
{
    public function __construct(
        private readonly ListManagersAction $list,
    ) {}

    public function index(Request $request): JsonResponse
    {
        return response()->json($this->list->execute($request->user()));
    }
}
