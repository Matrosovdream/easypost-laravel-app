<?php

use App\Actions\Profile\GetNotificationPrefsAction;
use App\Helpers\Profile\NotificationPrefsHelper;
use App\Models\User;

it('returns DEFAULTS when user has no prefs', function () {
    $action = new GetNotificationPrefsAction(new NotificationPrefsHelper());
    $user = new User();

    expect($action->execute($user)['data'])->toBe(NotificationPrefsHelper::DEFAULTS);
});

it('returns user prefs when set', function () {
    $action = new GetNotificationPrefsAction(new NotificationPrefsHelper());
    $user = new User();
    $user->notification_prefs = ['email.foo' => false];

    expect($action->execute($user)['data'])->toBe(['email.foo' => false]);
});
