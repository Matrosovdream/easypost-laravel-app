<?php

use App\Support\Rights;

it('has every role-assigned right defined in the catalog', function () {
    foreach (Rights::byRole() as $role => $rights) {
        foreach ($rights as $r) {
            expect(Rights::exists($r))
                ->toBeTrue("Role `{$role}` grants undefined right `{$r}`");
        }
    }
});

it('produces a non-empty catalog', function () {
    expect(Rights::CATALOG)
        ->toBeArray()
        ->not->toBeEmpty()
        ->and(count(Rights::CATALOG))->toBeGreaterThan(30);
});

it('groups are derived from the prefix', function () {
    foreach (Rights::catalog() as $entry) {
        $firstSegment = explode('.', $entry['right'])[0];
        expect($entry['group'])->toBe($firstSegment);
    }
});
