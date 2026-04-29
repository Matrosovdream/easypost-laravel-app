<?php

namespace App\Http\Controllers\Api\Batches;

use App\Actions\Batches\BuyBatchAction;
use App\Actions\Batches\CreateBatchAction;
use App\Actions\Batches\GenerateBatchLabelsAction;
use App\Actions\Batches\ListBatchesAction;
use App\Actions\Batches\ShowBatchAction;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\Batches\CreateBatchRequest;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class BatchesController extends Controller
{
    public function __construct(
        private readonly ListBatchesAction $list,
        private readonly ShowBatchAction $show,
        private readonly CreateBatchAction $create,
        private readonly BuyBatchAction $buy,
        private readonly GenerateBatchLabelsAction $labels,
    ) {}

    public function index(Request $request): JsonResponse
    {
        return response()->json($this->list->execute(
            $request->user(),
            $request->query('state'),
            (int) $request->query('per_page', 25),
        ));
    }

    public function show(int $id): JsonResponse
    {
        return response()->json($this->show->execute($id));
    }

    public function store(CreateBatchRequest $request): JsonResponse
    {
        return response()->json($this->create->execute(
            $request->user(),
            $request->input('shipment_ids', []),
            $request->input('reference'),
        ), 201);
    }

    public function buy(Request $request, int $id): JsonResponse
    {
        return response()->json($this->buy->execute($request->user(), $id));
    }

    public function generateLabels(int $id): JsonResponse
    {
        return response()->json($this->labels->execute($id));
    }
}
