<?php

namespace App\Repositories\Operations;

use App\Models\Batch;
use App\Repositories\AbstractRepo;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class BatchRepo extends AbstractRepo
{
    protected $withRelations = ['creator:id,name', 'scanForm', 'pickup'];

    public function __construct()
    {
        $this->model = new Batch();
    }

    public function paginateForTeam(int $teamId, ?string $state = null, int $perPage = 25): LengthAwarePaginator
    {
        $q = Batch::where('team_id', $teamId)->with('creator:id,name');
        if ($state) $q->where('state', $state);
        return $q->orderByDesc('id')->paginate($perPage);
    }

    public function findWithShipments(int $id): ?Batch
    {
        return Batch::with(['shipments.toAddress', 'creator:id,name', 'scanForm', 'pickup'])->find($id);
    }

    public function updateState(Batch $batch, string $state, ?array $statusSummary = null, ?string $labelUrl = null): Batch
    {
        $batch->forceFill(array_filter([
            'state' => $state,
            'status_summary' => $statusSummary,
            'label_pdf_s3_key' => $labelUrl,
        ], fn ($v) => $v !== null))->save();
        return $batch->fresh();
    }
}
