<?php

use App\Actions\Profile\UpdateNotificationPrefsAction;
use App\Models\User;

it('echoes back prefs (placeholder until persisted)', function () {
    $action = new UpdateNotificationPrefsAction();
    $user = new User();
    $prefs = ['email.x' => true];

    expect($action->execute($user, $prefs))->toBe(['data' => $prefs]);
});
