<?php

use App\Actions\Navigation\ListNavigationCountsAction;
use App\Helpers\Navigation\NavigationCountsHelper;
use App\Models\User;

it('delegates to NavigationCountsHelper::buildForUser', function () {
    $helper = mock(NavigationCountsHelper::class);
    $user = new User();
    $expected = ['approvalsCount' => 3];

    $helper->shouldReceive('buildForUser')->with($user)->once()->andReturn($expected);

    $action = new ListNavigationCountsAction($helper);
    expect($action->execute($user))->toBe($expected);
});
