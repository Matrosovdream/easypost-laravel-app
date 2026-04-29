<?php

namespace App\Actions\ScanForms;

use App\Helpers\ScanForms\ScanFormHelper;
use App\Repositories\Operations\ScanFormRepo;
use Illuminate\Support\Facades\Gate;

class ShowScanFormAction
{
    public function __construct(
        private readonly ScanFormRepo $scanForms,
        private readonly ScanFormHelper $helper,
    ) {}

    public function execute(int $id): array
    {
        $form = $this->scanForms->getModel()->newQuery()->find($id);
        abort_if(! $form, 404);
        Gate::authorize('view', $form);

        return $this->helper->toDetail($form);
    }
}
