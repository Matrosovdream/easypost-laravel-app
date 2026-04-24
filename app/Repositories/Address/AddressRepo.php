<?php

namespace App\Repositories\Address;

use App\Models\Address;
use App\Repositories\AbstractRepo;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class AddressRepo extends AbstractRepo
{
    public function __construct()
    {
        $this->model = new Address();
    }

    public function createForTeam(int $teamId, array $data): Address
    {
        return Address::create(array_merge($data, ['team_id' => $teamId]));
    }

    public function findInTeam(int $teamId, int $id): ?Address
    {
        return Address::where('team_id', $teamId)->find($id);
    }

    public function firstForTeam(int $teamId): ?Address
    {
        return Address::where('team_id', $teamId)->first();
    }

    public function paginateForTeam(int $teamId, ?string $search = null, int $perPage = 25): LengthAwarePaginator
    {
        $q = Address::where('team_id', $teamId);
        if ($search) {
            $q->where(function ($sub) use ($search) {
                $sub->where('name', 'ilike', "%{$search}%")
                    ->orWhere('company', 'ilike', "%{$search}%")
                    ->orWhere('street1', 'ilike', "%{$search}%")
                    ->orWhere('city', 'ilike', "%{$search}%");
            });
        }
        return $q->orderByDesc('id')->paginate($perPage);
    }

    public function updateAttributes(Address $address, array $data): Address
    {
        $address->fill($data)->save();
        return $address->fresh();
    }

    public function markVerified(Address $address, bool $success, ?array $verifications): Address
    {
        $address->forceFill([
            'verified' => $success,
            'verified_at' => $success ? now() : null,
            'verification' => $verifications,
        ])->save();
        return $address->fresh();
    }

    public function deleteRow(Address $address): void
    {
        $address->delete();
    }
}
