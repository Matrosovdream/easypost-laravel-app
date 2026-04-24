<?php

namespace App\Http\Controllers\Api\Batches;

use App\Actions\Batches\BuyBatchAction;
use App\Actions\Batches\CreateBatchAction;
use App\Actions\Batches\GenerateBatchLabelsAction;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\Batches\CreateBatchRequest;
use App\Models\Batch;
use App\Repositories\Operations\BatchRepo;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class BatchesController extends Controller
{
    public function __construct(
        private readonly CreateBatchAction $create,
        private readonly BuyBatchAction $buy,
        private readonly GenerateBatchLabelsAction $labels,
        private readonly BatchRepo $batches,
    ) {}

    public function index(Request $request): JsonResponse
    {
        $user = $request->user();
        $this->authorize('viewAny', Batch::class);

        $page = $this->batches->paginateForTeam(
            teamId: (int) $user->current_team_id,
            state: $request->query('state'),
            perPage: (int) $request->query('per_page', 25),
        );

        return response()->json([
            'data' => collect($page->items())->map(fn (Batch $b) => [
                'id' => $b->id,
                'reference' => $b->reference,
                'state' => $b->state,
                'num_shipments' => $b->num_shipments,
                'label_url' => $b->label_pdf_s3_key,
                'created_by' => $b->creator ? ['id' => $b->creator->id, 'name' => $b->creator->name] : null,
                'created_at' => $b->created_at?->toIso8601String(),
            ])->values(),
            'meta' => [
                'current_page' => $page->currentPage(),
                'last_page' => $page->lastPage(),
                'total' => $page->total(),
            ],
        ]);
    }

    public function show(Request $request, int $id): JsonResponse
    {
        $batch = $this->batches->findWithShipments($id);
        abort_if(! $batch, 404);
        $this->authorize('view', $batch);

        return response()->json([
            'id' => $batch->id,
            'reference' => $batch->reference,
            'state' => $batch->state,
            'num_shipments' => $batch->num_shipments,
            'label_url' => $batch->label_pdf_s3_key,
            'status_summary' => $batch->status_summary,
            'scan_form_id' => $batch->scan_form_id,
            'pickup_id' => $batch->pickup_id,
            'shipments' => $batch->shipments->map(fn ($s) => [
                'id' => $s->id,
                'status' => $s->status,
                'carrier' => $s->carrier,
                'service' => $s->service,
                'tracking_code' => $s->tracking_code,
                'reference' => $s->reference,
                'batch_status' => $s->pivot->batch_status,
                'batch_message' => $s->pivot->batch_message,
                'to_address' => $s->toAddress ? [
                    'city' => $s->toAddress->city,
                    'state' => $s->toAddress->state,
                    'country' => $s->toAddress->country,
                ] : null,
            ]),
        ]);
    }

    public function store(CreateBatchRequest $request): JsonResponse
    {
        $batch = $this->create->execute(
            $request->user(),
            $request->input('shipment_ids', []),
            $request->input('reference'),
        );
        return response()->json(['id' => $batch->id, 'state' => $batch->state], 201);
    }

    public function buy(Request $request, int $id): JsonResponse
    {
        $batch = $this->batches->getModel()->newQuery()->find($id);
        abort_if(! $batch, 404);
        $this->authorize('update', $batch);

        $batch = $this->buy->execute($request->user(), $batch);
        return response()->json(['id' => $batch->id, 'state' => $batch->state]);
    }

    public function generateLabels(Request $request, int $id): JsonResponse
    {
        $batch = $this->batches->getModel()->newQuery()->find($id);
        abort_if(! $batch, 404);
        $this->authorize('update', $batch);

        $batch = $this->labels->execute($batch);
        return response()->json(['id' => $batch->id, 'label_url' => $batch->label_pdf_s3_key]);
    }
}
