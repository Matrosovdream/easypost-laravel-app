<?php

namespace App\Actions\ScanForms;

use App\Helpers\ScanForms\ScanFormHelper;
use App\Models\ScanForm;
use App\Models\User;
use App\Repositories\Operations\ScanFormRepo;
use Illuminate\Support\Facades\Gate;

class ListScanFormsAction
{
    public function __construct(
        private readonly ScanFormRepo $scanForms,
        private readonly ScanFormHelper $helper,
    ) {}

    public function execute(User $user, int $perPage = 25): array
    {
        Gate::authorize('viewAny', ScanForm::class);

        $page = $this->scanForms->paginateForTeam(
            teamId: (int) $user->current_team_id,
            perPage: $perPage,
        );

        return $this->helper->toListPayload($page);
    }
}
