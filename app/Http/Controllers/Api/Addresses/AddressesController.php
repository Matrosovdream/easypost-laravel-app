<?php

namespace App\Http\Controllers\Api\Addresses;

use App\Actions\Addresses\CreateAndVerifyAddressAction;
use App\Actions\Addresses\VerifyExistingAddressAction;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\Addresses\CreateAddressRequest;
use App\Models\Address;
use App\Repositories\Address\AddressRepo;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AddressesController extends Controller
{
    public function __construct(
        private readonly CreateAndVerifyAddressAction $create,
        private readonly VerifyExistingAddressAction $verify,
        private readonly AddressRepo $addresses,
    ) {}

    public function index(Request $request): JsonResponse
    {
        $this->authorize('viewAny', Address::class);

        $page = $this->addresses->paginateForTeam(
            teamId: (int) $request->user()->current_team_id,
            search: $request->query('q'),
            perPage: (int) $request->query('per_page', 25),
        );

        return response()->json([
            'data' => collect($page->items())->map(fn (Address $a) => $this->map($a))->values(),
            'meta' => [
                'current_page' => $page->currentPage(),
                'last_page' => $page->lastPage(),
                'total' => $page->total(),
            ],
        ]);
    }

    public function show(Request $request, int $id): JsonResponse
    {
        $address = $this->addresses->getModel()->newQuery()->find($id);
        abort_if(! $address, 404);
        $this->authorize('view', $address);
        return response()->json($this->map($address));
    }

    public function store(CreateAddressRequest $request): JsonResponse
    {
        $address = $this->create->execute(
            $request->user(),
            $request->validated(),
            verify: (bool) $request->input('verify', true),
        );
        return response()->json($this->map($address), 201);
    }

    public function update(CreateAddressRequest $request, int $id): JsonResponse
    {
        $address = $this->addresses->getModel()->newQuery()->find($id);
        abort_if(! $address, 404);
        $this->authorize('view', $address);

        $updated = $this->addresses->updateAttributes($address, $request->validated());
        return response()->json($this->map($updated));
    }

    public function verify(Request $request, int $id): JsonResponse
    {
        $address = $this->addresses->getModel()->newQuery()->find($id);
        abort_if(! $address, 404);
        $this->authorize('verify', $address);

        $address = $this->verify->execute($address);
        return response()->json($this->map($address));
    }

    public function destroy(Request $request, int $id): JsonResponse
    {
        $address = $this->addresses->getModel()->newQuery()->find($id);
        abort_if(! $address, 404);
        $this->authorize('delete', $address);

        $this->addresses->deleteRow($address);
        return response()->json(['ok' => true]);
    }

    private function map(Address $a): array
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
}
