<?php

namespace App\Actions\Returns;

use App\Helpers\Returns\ReturnHelper;
use App\Repositories\Care\ReturnRequestRepo;
use Illuminate\Support\Facades\Gate;

class ShowReturnAction
{
    public function __construct(
        private readonly ReturnRequestRepo $returns,
        private readonly ReturnHelper $helper,
    ) {}

    public function execute(int $id): array
    {
        $return = $this->returns->findWithDetails($id);
        abort_if(! $return, 404);
        Gate::authorize('view', $return);

        return $this->helper->toDetail($return);
    }
}
