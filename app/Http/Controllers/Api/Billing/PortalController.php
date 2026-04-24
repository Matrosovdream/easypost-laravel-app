<?php

namespace App\Http\Controllers\Api\Billing;

use App\Http\Controllers\Controller;
use App\Repositories\Team\TeamRepo;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PortalController extends Controller
{
    public function __construct(private readonly TeamRepo $teams) {}

    public function __invoke(Request $request): JsonResponse
    {
        abort_unless(in_array('billing.manage', $request->user()->rights(), true), 403);

        $mapped = $this->teams->getByID($request->user()->current_team_id);
        abort_if(! $mapped, 404);
        /** @var \App\Models\Team $team */
        $team = $mapped['Model'];

        try {
            $url = $team->billingPortalUrl(config('billing.success_url'));
            return response()->json(['url' => $url]);
        } catch (\Throwable $e) {
            if (app()->environment(['local', 'testing'])) {
                return response()->json([
                    'url' => url('/dashboard/settings/billing?portal=simulated'),
                    'simulated' => true,
                ]);
            }
            return response()->json(['message' => 'Could not open portal: '.$e->getMessage()], 502);
        }
    }
}
