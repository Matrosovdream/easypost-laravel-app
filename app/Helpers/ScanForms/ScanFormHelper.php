<?php

namespace App\Helpers\ScanForms;

use App\Models\ScanForm;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class ScanFormHelper
{
    public function toListItem(ScanForm $f): array
    {
        return [
            'id' => $f->id,
            'carrier' => $f->carrier,
            'status' => $f->status,
            'form_url' => $f->form_pdf_s3_key,
            'tracking_codes' => $f->tracking_codes,
            'created_at' => $f->created_at?->toIso8601String(),
        ];
    }

    public function toDetail(ScanForm $f): array
    {
        return [
            'id' => $f->id,
            'carrier' => $f->carrier,
            'status' => $f->status,
            'form_url' => $f->form_pdf_s3_key,
            'tracking_codes' => $f->tracking_codes,
            'from_address_id' => $f->from_address_id,
            'created_at' => $f->created_at?->toIso8601String(),
        ];
    }

    public function toCreatedPayload(ScanForm $f): array
    {
        return [
            'id' => $f->id,
            'status' => $f->status,
            'form_url' => $f->form_pdf_s3_key,
        ];
    }

    public function toListPayload(LengthAwarePaginator $page): array
    {
        return [
            'data' => collect($page->items())->map(fn (ScanForm $f) => $this->toListItem($f))->values(),
            'meta' => [
                'current_page' => $page->currentPage(),
                'last_page' => $page->lastPage(),
                'total' => $page->total(),
            ],
        ];
    }
}
