<?php

namespace App\Http\Controllers\Api\Billing;

use App\Http\Controllers\Controller;
use App\Repositories\Team\TeamRepo;
use App\Services\Billing\PlanCaps;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PlanController extends Controller
{
    public function __construct(
        private readonly PlanCaps $caps,
        private readonly TeamRepo $teams,
    ) {}

    public function __invoke(Request $request): JsonResponse
    {
        $mapped = $this->teams->getByID($request->user()->current_team_id);
        abort_if(! $mapped, 404);
        $team = $mapped['Model'];

        $cap = $this->caps->capForPlan((string) $team->plan);
        $used = $this->caps->usageForTeamThisMonth($team->id);

        return response()->json([
            'plan' => $team->plan,
            'status' => $team->status,
            'mode' => $team->mode,
            'trial_ends_at' => $team->trial_ends_at?->toIso8601String(),
            'stripe_customer_id' => $team->stripe_customer_id,
            'usage' => [
                'used' => $used,
                'cap' => $cap,
                'remaining' => $cap === null ? null : max(0, $cap - $used),
                'reset_at' => now()->startOfMonth()->addMonth()->toIso8601String(),
            ],
            'available_plans' => array_keys(config('billing.prices')),
        ]);
    }
}
