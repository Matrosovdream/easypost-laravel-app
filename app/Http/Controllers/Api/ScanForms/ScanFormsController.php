<?php

namespace App\Http\Controllers\Api\ScanForms;

use App\Actions\ScanForms\GenerateScanFormAction;
use App\Http\Controllers\Controller;
use App\Models\ScanForm;
use App\Repositories\Operations\ScanFormRepo;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ScanFormsController extends Controller
{
    public function __construct(
        private readonly GenerateScanFormAction $generate,
        private readonly ScanFormRepo $scanForms,
    ) {}

    public function index(Request $request): JsonResponse
    {
        $user = $request->user();
        $this->authorize('viewAny', ScanForm::class);

        $page = $this->scanForms->paginateForTeam(
            teamId: (int) $user->current_team_id,
            perPage: (int) $request->query('per_page', 25),
        );

        return response()->json([
            'data' => collect($page->items())->map(fn (ScanForm $f) => [
                'id' => $f->id,
                'carrier' => $f->carrier,
                'status' => $f->status,
                'form_url' => $f->form_pdf_s3_key,
                'tracking_codes' => $f->tracking_codes,
                'created_at' => $f->created_at?->toIso8601String(),
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
        $form = $this->scanForms->getModel()->newQuery()->find($id);
        abort_if(! $form, 404);
        $this->authorize('view', $form);

        return response()->json([
            'id' => $form->id,
            'carrier' => $form->carrier,
            'status' => $form->status,
            'form_url' => $form->form_pdf_s3_key,
            'tracking_codes' => $form->tracking_codes,
            'from_address_id' => $form->from_address_id,
            'created_at' => $form->created_at?->toIso8601String(),
        ]);
    }

    public function store(Request $request): JsonResponse
    {
        $this->authorize('create', ScanForm::class);

        $request->validate([
            'shipment_ids' => ['required', 'array', 'min:1'],
            'shipment_ids.*' => ['integer'],
        ]);

        try {
            $form = $this->generate->execute($request->user(), $request->input('shipment_ids'));
            return response()->json([
                'id' => $form->id,
                'status' => $form->status,
                'form_url' => $form->form_pdf_s3_key,
            ], 201);
        } catch (\RuntimeException $e) {
            return response()->json(['message' => $e->getMessage()], 422);
        }
    }
}
