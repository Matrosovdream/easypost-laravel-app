<?php

namespace App\Http\Controllers\Api\Navigation;

use App\Actions\Navigation\ListNavigationCountsAction;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CountsController extends Controller
{
    public function __construct(private readonly ListNavigationCountsAction $action) {}

    public function __invoke(Request $request): JsonResponse
    {
        return response()->json($this->action->execute($request->user()));
    }
}
