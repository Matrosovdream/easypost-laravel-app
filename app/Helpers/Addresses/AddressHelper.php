<?php

namespace App\Helpers\Addresses;

use App\Models\Address;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class AddressHelper
{
    public function toListItem(Address $a): array
    {
        return [
            'id' => $a->id,
            'name' => $a->name,
            'company' => $a->company,
            'street1' => $a->street1,
            'street2' => $a->street2,
            'city' => $a->city,
            'state' => $a->state,
            'zip' => $a->zip,
            'country' => $a->country,
            'phone' => $a->phone,
            'email' => $a->email,
            'residential' => $a->residential,
            'verified' => (bool) $a->verified,
            'verified_at' => $a->verified_at?->toIso8601String(),
            'ep_address_id' => $a->ep_address_id,
            'client_id' => $a->client_id,
            'created_at' => $a->created_at?->toIso8601String(),
        ];
    }

    public function toDetail(Address $a): array
    {
        return $this->toListItem($a);
    }

    public function toListPayload(LengthAwarePaginator $page): array
    {
        return [
            'data' => collect($page->items())->map(fn (Address $a) => $this->toListItem($a))->values(),
            'meta' => [
                'current_page' => $page->currentPage(),
                'last_page' => $page->lastPage(),
                'total' => $page->total(),
            ],
        ];
    }
}
