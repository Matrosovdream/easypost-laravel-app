<?php

namespace App\Actions\Reports;

use App\Helpers\Reports\ReportHelper;
use App\Models\User;
use App\Repositories\Infra\ReportRepo;

class ListReportsAction
{
    public function __construct(
        private readonly ReportRepo $reports,
        private readonly ReportHelper $helper,
    ) {}

    public function execute(User $user): array
    {
        abort_unless(in_array('reports.view', $user->rights(), true), 403);

        return $this->helper->toListPayload(
            $this->reports->listForTeam((int) $user->current_team_id)
        );
    }
}
