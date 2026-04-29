<?php

namespace App\Http\Controllers\Api\Returns;

use App\Actions\Returns\ApproveReturnAction;
use App\Actions\Returns\CreateReturnRequestAction;
use App\Actions\Returns\DeclineReturnAction;
use App\Actions\Returns\ListReturnsAction;
use App\Actions\Returns\ShowReturnAction;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\Returns\CreateReturnRequest;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ReturnsController extends Controller
{
    public function __construct(
        private readonly ListReturnsAction $list,
        private readonly ShowReturnAction $show,
        private readonly CreateReturnRequestAction $create,
        private readonly ApproveReturnAction $approve,
        private readonly DeclineReturnAction $decline,
    ) {}

    public function index(Request $request): JsonResponse
    {
        return response()->json($this->list->execute(
            $request->user(),
            $request->query('status'),
            (int) $request->query('per_page', 25),
        ));
    }

    public function show(int $id): JsonResponse
    {
        return response()->json($this->show->execute($id));
    }

    public function store(CreateReturnRequest $request): JsonResponse
    {
        return response()->json($this->create->execute($request->user(), $request->validated()), 201);
    }

    public function approve(Request $request, int $id): JsonResponse
    {
        try {
            return response()->json($this->approve->execute($request->user(), $id));
        } catch (\Symfony\Component\HttpKernel\Exception\HttpException $e) {
            throw $e;
        } catch (\RuntimeException $e) {
            return response()->json(['message' => $e->getMessage()], 422);
        }
    }

    public function decline(Request $request, int $id): JsonResponse
    {
        $request->validate(['reason' => ['nullable', 'string', 'max:500']]);

        try {
            return response()->json($this->decline->execute($request->user(), $id, $request->input('reason')));
        } catch (\Symfony\Component\HttpKernel\Exception\HttpException $e) {
            throw $e;
        } catch (\RuntimeException $e) {
            return response()->json(['message' => $e->getMessage()], 422);
        }
    }
}
