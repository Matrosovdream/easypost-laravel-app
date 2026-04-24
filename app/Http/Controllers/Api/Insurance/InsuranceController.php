<?php

namespace App\Http\Controllers\Api\Insurance;

use App\Actions\Insurance\CreateStandaloneInsuranceAction;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\Insurance\CreateInsuranceRequest;
use App\Models\Insurance;
use App\Repositories\Care\InsuranceRepo;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class InsuranceController extends Controller
{
    public function __construct(
        private readonly CreateStandaloneInsuranceAction $create,
        private readonly InsuranceRepo $insurances,
    ) {}

    public function index(Request $request): JsonResponse
    {
        $this->authorize('viewAny', Insurance::class);
        $user = $request->user();

        $page = $this->insurances->paginateForTeam(
            teamId: (int) $user->current_team_id,
            perPage: (int) $request->query('per_page', 25),
        );

        return response()->json([
            'data' => collect($page->items())->map(fn (Insurance $i) => [
                'id' => $i->id,
                'tracking_code' => $i->tracking_code,
                'carrier' => $i->carrier,
                'amount_cents' => $i->amount_cents,
                'fee_cents' => $i->fee_cents,
                'provider' => $i->provider,
                'status' => $i->status,
                'reference' => $i->reference,
                'shipment_id' => $i->shipment_id,
                'created_at' => $i->created_at?->toIso8601String(),
            ])->values(),
            'meta' => [
                'current_page' => $page->currentPage(),
                'last_page' => $page->lastPage(),
                'total' => $page->total(),
            ],
        ]);
    }

    public function store(CreateInsuranceRequest $request): JsonResponse
    {
        $insurance = $this->create->execute($request->user(), $request->validated());
        return response()->json([
            'id' => $insurance->id,
            'status' => $insurance->status,
            'ep_insurance_id' => $insurance->ep_insurance_id,
            'messages' => $insurance->messages,
        ], 201);
    }
}
