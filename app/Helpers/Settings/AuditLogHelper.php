<?php

namespace App\Helpers\Settings;

use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class AuditLogHelper
{
    public function toListItem(object $r): array
    {
        return [
            'id' => $r->id,
            'action' => $r->action,
            'user' => $r->user_name ? ['name' => $r->user_name, 'email' => $r->user_email] : null,
            'subject_type' => $r->subject_type,
            'subject_id' => $r->subject_id,
            'meta' => $r->meta ? json_decode($r->meta, true) : null,
            'ip' => $r->ip,
            'created_at' => $r->created_at,
        ];
    }

    public function toListPayload(LengthAwarePaginator $page): array
    {
        return [
            'data' => collect($page->items())->map(fn ($r) => $this->toListItem($r))->values(),
            'meta' => [
                'current_page' => $page->currentPage(),
                'last_page' => $page->lastPage(),
                'total' => $page->total(),
            ],
        ];
    }
}
