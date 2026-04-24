<?php

namespace App\Http\Controllers\Api\Navigation;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class CountsController extends Controller
{
    public function __invoke(Request $request): JsonResponse
    {
        /** @var User $user */
        $user = $request->user();
        $teamId = $user->current_team_id;
        $rights = $user->rights();

        $counts = [
            'approvalsCount' => 0,
            'exceptionsCount' => 0,
            'returnsCount' => 0,
            'claimsCount' => 0,
            'queueCount' => 0,
            'printReady' => 0,
        ];

        if (! $teamId) {
            return response()->json($counts);
        }

        if (in_array('shipments.approve', $rights, true)) {
            $counts['approvalsCount'] = DB::table('approvals')
                ->where('team_id', $teamId)
                ->where('status', 'pending')
                ->count();
        }

        if (in_array('trackers.view', $rights, true) && DB::getSchemaBuilder()->hasTable('trackers')) {
            $counts['exceptionsCount'] = DB::table('trackers')
                ->where('team_id', $teamId)
                ->whereIn('status', ['unknown', 'failure', 'return_to_sender'])
                ->count();
        }

        if (in_array('labels.print', $rights, true)) {
            $counts['printReady'] = DB::table('shipments')
                ->where('team_id', $teamId)
                ->where('status', 'purchased')
                ->whereNull('packed_at')
                ->count();
        }

        return response()->json($counts);
    }
}
