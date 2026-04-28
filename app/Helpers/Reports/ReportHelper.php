<?php

namespace App\Helpers\Reports;

use Illuminate\Support\Collection;

class ReportHelper
{
    public function toListItem(object $r): array
    {
        return [
            'id' => $r->id,
            'type' => $r->type,
            'status' => $r->status,
            'start_date' => $r->start_date,
            'end_date' => $r->end_date,
            's3_key' => $r->s3_key,
            'created_at' => $r->created_at,
        ];
    }

    public function toListPayload(Collection $rows): array
    {
        return [
            'data' => $rows->map(fn ($r) => $this->toListItem($r))->values(),
        ];
    }
}
