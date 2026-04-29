<?php

namespace App\Repositories\Care;

use App\Models\ReturnRequest;
use App\Repositories\AbstractRepo;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class ReturnRequestRepo extends AbstractRepo
{
    protected $withRelations = [
        'originalShipment:id,reference,tracking_code,from_address_id,to_address_id,parcel_id,carrier',
        'returnShipment:id,reference,status,tracking_code',
        'creator:id,name',
        'approver:id,name',
    ];

    public function __construct()
    {
        $this->model = new ReturnRequest();
    }

    public function paginateForTeam(int $teamId, ?int $clientScope = null, ?string $status = null, int $perPage = 25): LengthAwarePaginator
    {
        $q = ReturnRequest::where('team_id', $teamId)->with($this->withRelations);
        if ($clientScope !== null) $q->where('client_id', $clientScope);
        if ($status) $q->where('status', $status);
        return $q->orderByDesc('id')->paginate($perPage);
    }

    public function findWithDetails(int $id): ?ReturnRequest
    {
        return ReturnRequest::with([
            'originalShipment.toAddress',
            'originalShipment.fromAddress',
            'originalShipment.parcel',
            'returnShipment.toAddress',
            'creator:id,name',
            'approver:id,name',
        ])->find($id);
    }

    public function markApproved(ReturnRequest $return, int $userId, int $returnShipmentId): ReturnRequest
    {
        $return->forceFill([
            'status' => 'approved',
            'return_shipment_id' => $returnShipmentId,
            'approved_by' => $userId,
            'approved_at' => now(),
        ])->save();
        return $return->fresh(['returnShipment', 'approver']);
    }

    public function markDeclined(ReturnRequest $return, int $userId, ?string $notes = null): ReturnRequest
    {
        $return->forceFill([
            'status' => 'declined',
            'approved_by' => $userId,
            'approved_at' => now(),
            'notes' => $notes,
        ])->save();
        return $return->fresh();
    }
}
