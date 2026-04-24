<?php

namespace App\Http\Controllers\Api\Billing;

use App\Http\Controllers\Controller;
use App\Repositories\Team\TeamRepo;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CheckoutController extends Controller
{
    public function __construct(private readonly TeamRepo $teams) {}

    public function __invoke(Request $request): JsonResponse
    {
        abort_unless(in_array('billing.manage', $request->user()->rights(), true), 403);

        $v = $request->validate([
            'plan' => ['required', 'string'],
        ]);

        $priceId = config("billing.prices.{$v['plan']}");
        if (! $priceId || str_contains((string) $priceId, 'placeholder')) {
            if (app()->environment(['local', 'testing'])) {
                return response()->json([
                    'url' => url("/dashboard/settings/billing?checkout=simulated&plan={$v['plan']}"),
                    'simulated' => true,
                ]);
            }
            return response()->json(['message' => 'Unknown plan.'], 422);
        }

        $mapped = $this->teams->getByID($request->user()->current_team_id);
        abort_if(! $mapped, 404);
        /** @var \App\Models\Team $team */
        $team = $mapped['Model'];

        try {
            $session = $team->newSubscription('default', $priceId)
                ->checkout([
                    'success_url' => config('billing.success_url'),
                    'cancel_url' => config('billing.cancel_url'),
                ]);

            return response()->json(['url' => $session->url]);
        } catch (\Throwable $e) {
            return response()->json(['message' => 'Checkout failed: '.$e->getMessage()], 502);
        }
    }
}
