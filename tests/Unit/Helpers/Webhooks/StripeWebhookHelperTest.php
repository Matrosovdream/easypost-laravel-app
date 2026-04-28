<?php

use App\Helpers\Webhooks\StripeWebhookHelper;
use App\Models\Team;
use App\Repositories\Team\TeamRepo;

beforeEach(function () {
    $this->teams = mock(TeamRepo::class);
    $this->helper = new StripeWebhookHelper($this->teams);
});

it('buildEventRow shapes a Stripe webhook log row', function () {
    $row = $this->helper->buildEventRow('evt_1', 'customer.subscription.updated', ['id' => 'evt_1']);
    expect($row)->toMatchArray([
        'source' => 'stripe',
        'ep_event_id' => 'evt_1',
        'description' => 'customer.subscription.updated',
        'signature_valid' => true,
    ]);
});

it('onCheckoutComplete updates team subscription id', function () {
    $team = new Team(['stripe_subscription_id' => null]);
    $team->id = 1;

    $this->teams->shouldReceive('getByStripeCustomerId')->with('cus_x')->andReturn($team);
    $this->teams->shouldReceive('update')
        ->withArgs(fn ($id, $data) => $id === 1 && $data['stripe_subscription_id'] === 'sub_new')
        ->once();

    $this->helper->onCheckoutComplete(['customer' => 'cus_x', 'subscription' => 'sub_new']);
});

it('onCheckoutComplete is a no-op when customer missing', function () {
    $this->teams->shouldNotReceive('getByStripeCustomerId');
    $this->helper->onCheckoutComplete([]);
});

it('onSubscriptionChange maps active status to active', function () {
    $team = new Team(['status' => 'active', 'stripe_subscription_id' => 'old']);
    $team->id = 1;

    config()->set('billing.prices', ['team' => 'price_team_1']);

    $this->teams->shouldReceive('getByStripeCustomerId')->with('cus_x')->andReturn($team);
    $this->teams->shouldReceive('update')
        ->withArgs(function ($id, $data) {
            return $id === 1
                && $data['plan'] === 'team'
                && $data['status'] === 'active'
                && $data['stripe_subscription_id'] === 'sub_a';
        })
        ->once();

    $this->helper->onSubscriptionChange([
        'customer' => 'cus_x',
        'id' => 'sub_a',
        'status' => 'active',
        'items' => ['data' => [['price' => ['id' => 'price_team_1']]]],
    ]);
});

it('onSubscriptionChange maps past_due → locked', function () {
    $team = new Team(['status' => 'active']);
    $team->id = 1;
    config()->set('billing.prices', ['team' => 'price_team_1']);

    $this->teams->shouldReceive('getByStripeCustomerId')->andReturn($team);
    $this->teams->shouldReceive('update')
        ->withArgs(fn ($id, $data) => $data['status'] === 'locked')
        ->once();

    $this->helper->onSubscriptionChange([
        'customer' => 'cus_x',
        'status' => 'past_due',
        'items' => ['data' => [['price' => ['id' => 'price_team_1']]]],
    ]);
});

it('onSubscriptionChange aborts when price not in config', function () {
    config()->set('billing.prices', ['team' => 'price_team_1']);

    $this->teams->shouldNotReceive('getByStripeCustomerId');
    $this->helper->onSubscriptionChange([
        'customer' => 'cus_x',
        'items' => ['data' => [['price' => ['id' => 'price_unknown']]]],
    ]);
});

it('onSubscriptionCancelled downgrades plan to starter and clears sub id', function () {
    $team = new Team();
    $team->id = 1;

    $this->teams->shouldReceive('getByStripeCustomerId')->with('cus_x')->andReturn($team);
    $this->teams->shouldReceive('update')
        ->withArgs(fn ($id, $data) =>
            $id === 1
            && $data['plan'] === 'starter'
            && $data['stripe_subscription_id'] === null)
        ->once();

    $this->helper->onSubscriptionCancelled(['customer' => 'cus_x']);
});
