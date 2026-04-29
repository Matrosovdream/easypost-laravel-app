<?php

use App\Actions\Insurance\CreateStandaloneInsuranceAction;
use App\Mixins\Integrations\EasyPost\EasyPostClient;
use App\Models\Insurance;
use App\Models\User;
use App\Repositories\Care\InsuranceRepo;

beforeEach(function () {
    $this->ep = mock(EasyPostClient::class);
    $this->repo = mock(InsuranceRepo::class);
    $this->action = new CreateStandaloneInsuranceAction($this->ep, $this->repo);
});

it('throws when amount is non-positive', function () {
    $user = new User();
    $user->current_team_id = 3;

    expect(fn () => $this->action->execute($user, [
        'amount_cents' => 0, 'tracking_code' => 'TC', 'carrier' => 'USPS',
    ]))->toThrow(RuntimeException::class, 'positive');
});

it('falls through when EP fails and persists locally with messages', function () {
    $this->ep->shouldReceive('createInsurance')->andThrow(new \RuntimeException('boom'));

    $insurance = new Insurance(['status' => 'failed']);
    $insurance->id = 1;

    $this->repo->shouldReceive('create')
        ->withArgs(fn ($data) => $data['status'] === 'failed' && isset($data['messages']['error']))
        ->andReturn(['Model' => $insurance]);

    $user = new User();
    $user->current_team_id = 3;

    $out = $this->action->execute($user, [
        'amount_cents' => 1000, 'tracking_code' => 'TC', 'carrier' => 'USPS',
    ]);
    expect($out)->toBeInstanceOf(Insurance::class);
});
