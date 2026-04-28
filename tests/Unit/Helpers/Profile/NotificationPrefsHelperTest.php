<?php

use App\Helpers\Profile\NotificationPrefsHelper;
use App\Models\User;

beforeEach(function () {
    $this->helper = new NotificationPrefsHelper();
});

it('exposes default prefs', function () {
    expect(NotificationPrefsHelper::DEFAULTS)->toHaveKey('email.shipment.delivered');
    expect(NotificationPrefsHelper::DEFAULTS)->toHaveKey('email.return.status');
});

it('forUser returns user prefs when present', function () {
    $user = new User();
    $user->notification_prefs = ['email.foo' => false];
    expect($this->helper->forUser($user))->toBe(['email.foo' => false]);
});

it('forUser returns DEFAULTS when user prefs are null', function () {
    $user = new User();
    expect($this->helper->forUser($user))->toBe(NotificationPrefsHelper::DEFAULTS);
});
