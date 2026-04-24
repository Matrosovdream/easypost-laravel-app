<?php

namespace App\Http\Controllers\Api\Settings;

use App\Http\Controllers\Controller;
use App\Repositories\Infra\AuditLogRepo;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AuditLogController extends Controller
{
    public function __construct(private readonly AuditLogRepo $auditLogs) {}

    public function __invoke(Request $request): JsonResponse
    {
        abort_unless((bool) array_intersect($request->user()->rights(), ['audit_log.view.any', 'audit_log.view.own']), 403);

        $userScope = in_array('audit_log.view.any', $request->user()->rights(), true)
            ? null
            : (int) $request->user()->id;

        $page = $this->auditLogs->paginateForTeam(
            teamId: (int) $request->user()->current_team_id,
            userIdScope: $userScope,
            actionPrefix: $request->query('action'),
            perPage: (int) $request->query('per_page', 50),
        );

        return response()->json([
            'data' => collect($page->items())->map(fn ($r) => [
                'id' => $r->id,
                'action' => $r->action,
                'user' => $r->user_name ? ['name' => $r->user_name, 'email' => $r->user_email] : null,
                'subject_type' => $r->subject_type,
                'subject_id' => $r->subject_id,
                'meta' => $r->meta ? json_decode($r->meta, true) : null,
                'ip' => $r->ip,
                'created_at' => $r->created_at,
            ])->values(),
            'meta' => [
                'current_page' => $page->currentPage(),
                'last_page' => $page->lastPage(),
                'total' => $page->total(),
            ],
        ]);
    }
}
