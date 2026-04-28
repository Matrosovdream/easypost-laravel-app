<?php

namespace App\Actions\Navigation;

use App\Helpers\Navigation\NavigationCountsHelper;
use App\Models\User;

class ListNavigationCountsAction
{
    public function __construct(
        private readonly NavigationCountsHelper $helper,
    ) {}

    public function execute(User $user): array
    {
        return $this->helper->buildForUser($user);
    }
}
