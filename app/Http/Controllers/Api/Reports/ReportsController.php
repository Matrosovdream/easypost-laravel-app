<?php

namespace App\Http\Controllers\Api\Reports;

use App\Http\Controllers\Controller;
use App\Repositories\Infra\ReportRepo;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ReportsController extends Controller
{
    public function __construct(private readonly ReportRepo $reports) {}

    public function index(Request $request): JsonResponse
    {
        abort_unless(in_array('reports.view', $request->user()->rights(), true), 403);

        $rows = $this->reports->listForTeam((int) $request->user()->current_team_id);

        return response()->json([
            'data' => $rows->map(fn ($r) => [
                'id' => $r->id,
                'type' => $r->type,
                'status' => $r->status,
                'start_date' => $r->start_date,
                'end_date' => $r->end_date,
                's3_key' => $r->s3_key,
                'created_at' => $r->created_at,
            ])->values(),
        ]);
    }

    public function store(Request $request): JsonResponse
    {
        abort_unless(in_array('reports.create', $request->user()->rights(), true), 403);

        $v = $request->validate([
            'type' => ['required', 'in:shipment,tracker,payment_log,refund'],
            'start_date' => ['required', 'date'],
            'end_date' => ['required', 'date', 'after_or_equal:start_date'],
        ]);

        $id = $this->reports->create([
            'team_id' => $request->user()->current_team_id,
            'type' => $v['type'],
            'start_date' => $v['start_date'],
            'end_date' => $v['end_date'],
            'requested_by' => $request->user()->id,
        ]);

        return response()->json(['id' => $id, 'status' => 'queued'], 201);
    }
}
