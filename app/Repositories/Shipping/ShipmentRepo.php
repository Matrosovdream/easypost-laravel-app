<?php

namespace App\Repositories\Shipping;

use App\Models\Shipment;
use App\Repositories\AbstractRepo;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class ShipmentRepo extends AbstractRepo
{
    protected $withRelations = ['toAddress', 'fromAddress', 'parcel', 'client', 'assignee', 'requester'];

    public function __construct()
    {
        $this->model = new Shipment();
    }

    public function query()
    {
        return $this->model->with($this->withRelations);
    }

    public function queryUnscoped()
    {
        return $this->model->withoutGlobalScopes()->with($this->withRelations);
    }

    public function findUnscoped(int $id, array $with = []): ?Shipment
    {
        return Shipment::withoutGlobalScopes()
            ->with($with ?: $this->withRelations)
            ->find($id);
    }

    public function findByReference(string $reference): ?Shipment
    {
        return Shipment::withoutGlobalScopes()->where('reference', $reference)->first();
    }

    public function findByEpShipmentId(string $epShipmentId): ?Shipment
    {
        return Shipment::withoutGlobalScopes()->where('ep_shipment_id', $epShipmentId)->first();
    }

    public function findByTrackingCode(string $code): ?Shipment
    {
        return Shipment::withoutGlobalScopes()->where('tracking_code', $code)->first();
    }

    public function inTeam(int $teamId, array $whereIn = []): \Illuminate\Support\Collection
    {
        $q = Shipment::withoutGlobalScopes()->where('team_id', $teamId);
        if (! empty($whereIn['id'])) {
            $q->whereIn('id', $whereIn['id']);
        }
        if (! empty($whereIn['status'])) {
            $q->whereIn('status', $whereIn['status']);
        }
        return $q->get();
    }

    /**
     * List endpoint backbone — respects the team scope applied at the Eloquent
     * global-scope layer (for role-based narrowing). `queryScoped()` is used by
     * the REST list controller; `forTeam()` is the un-narrowed team fetch.
     */
    public function paginateScoped(array $filter = [], array $with = [], int $perPage = 25): LengthAwarePaginator
    {
        $q = Shipment::query()->with($with ?: ['toAddress', 'assignee', 'requester']);
        if (! empty($filter['status'])) $q->where('status', $filter['status']);
        if (! empty($filter['carrier'])) $q->where('carrier', $filter['carrier']);
        if (! empty($filter['q'])) {
            $q->where(function ($sub) use ($filter) {
                $sub->where('reference', 'ilike', "%{$filter['q']}%")
                    ->orWhere('tracking_code', 'ilike', "%{$filter['q']}%");
            });
        }
        return $q->orderByDesc('id')->paginate($perPage);
    }

    public function paginateAssignedTo(int $userId, array $statuses, int $perPage = 25): LengthAwarePaginator
    {
        return Shipment::query()
            ->with(['toAddress'])
            ->where('assigned_to', $userId)
            ->whereIn('status', $statuses)
            ->orderBy('created_at')
            ->paginate($perPage);
    }

    /**
     * Print-queue slice: purchased shipments that haven't been packed yet, limited
     * to 100 for UI responsiveness. Respects the global team-scope + role narrowing.
     */
    public function printQueue(int $limit = 100): \Illuminate\Database\Eloquent\Collection
    {
        return Shipment::query()
            ->with('toAddress:id,name,city,state,country')
            ->where('status', 'purchased')
            ->whereNull('packed_at')
            ->orderBy('created_at')
            ->limit($limit)
            ->get();
    }

    public function forTeam(int $teamId, array $filter = [], int $paginate = 25)
    {
        $query = $this->model->withoutGlobalScopes()
            ->with($this->withRelations)
            ->where('team_id', $teamId);

        $query = $this->applyFilter($query, $filter);

        return $this->mapItems($query->orderByDesc('id')->paginate($paginate));
    }

    public function markRequested(array $data): Shipment
    {
        return Shipment::withoutGlobalScopes()->create($data);
    }

    public function markPurchased(int $id, array $data): ?Shipment
    {
        $s = Shipment::withoutGlobalScopes()->find($id);
        if (! $s) return null;
        $s->fill($data)->save();
        return $s->fresh($this->withRelations);
    }

    public function markPacked(int $id, int $userId): ?Shipment
    {
        $s = Shipment::withoutGlobalScopes()->find($id);
        if (! $s) return null;
        $s->forceFill([
            'status' => 'packed',
            'packed_at' => now(),
            'assigned_to' => $s->assigned_to ?: $userId,
        ])->save();
        return $s->fresh($this->withRelations);
    }

    public function assign(int $id, ?int $userId): ?Shipment
    {
        $s = Shipment::withoutGlobalScopes()->find($id);
        if (! $s) return null;
        $s->forceFill(['assigned_to' => $userId])->save();
        return $s->fresh($this->withRelations);
    }

    public function markRefundRequested(int $id): ?Shipment
    {
        $s = Shipment::withoutGlobalScopes()->find($id);
        if (! $s) return null;
        $s->forceFill([
            'refund_status' => 'submitted',
            'refund_submitted_at' => now(),
            'status' => 'voided',
        ])->save();
        return $s->fresh($this->withRelations);
    }

    public function updateStatus(Shipment $shipment, array $data): Shipment
    {
        $shipment->forceFill($data)->save();
        return $shipment->fresh($this->withRelations);
    }

    public function mapItem($item)
    {
        if (empty($item)) {
            return null;
        }
        return [
            'id' => $item->id,
            'team_id' => $item->team_id,
            'client_id' => $item->client_id,
            'status' => $item->status,
            'carrier' => $item->carrier,
            'service' => $item->service,
            'tracking_code' => $item->tracking_code,
            'cost_cents' => $item->cost_cents,
            'reference' => $item->reference,
            'assigned_to' => $item->assigned_to,
            'requested_by' => $item->requested_by,
            'created_at' => $item->created_at,
            'Model' => $item,
        ];
    }
}
