<?php

namespace App\Actions\Reports;

use App\Models\User;
use App\Repositories\Infra\ReportRepo;

class CreateReportAction
{
    public function __construct(
        private readonly ReportRepo $reports,
    ) {}

    public function execute(User $user, array $input): array
    {
        abort_unless(in_array('reports.create', $user->rights(), true), 403);

        $id = $this->reports->create([
            'team_id' => $user->current_team_id,
            'type' => $input['type'],
            'start_date' => $input['start_date'],
            'end_date' => $input['end_date'],
            'requested_by' => $user->id,
        ]);

        return ['id' => $id, 'status' => 'queued'];
    }
}
