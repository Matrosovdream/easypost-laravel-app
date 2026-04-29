<?php

use App\Actions\Webhooks\HandleStripeWebhookAction;
use App\Helpers\Webhooks\StripeWebhookHelper;
use App\Repositories\Infra\WebhookEventRepo;
use Illuminate\Http\Request;

beforeEach(function () {
    $this->webhookEvents = mock(WebhookEventRepo::class);
    $this->helper = mock(StripeWebhookHelper::class);
    $this->action = new HandleStripeWebhookAction($this->webhookEvents, $this->helper);
});

it('dispatches checkout.session.completed → onCheckoutComplete', function () {
    $payload = [
        'id' => 'evt_1', 'type' => 'checkout.session.completed',
        'data' => ['object' => ['customer' => 'cus_x']],
    ];
    $request = Request::create('/x', 'POST', content: json_encode($payload));

    $this->helper->shouldReceive('buildEventRow')->andReturn([]);
    $this->helper->shouldReceive('onCheckoutComplete')->once()->with(['customer' => 'cus_x']);
    $this->webhookEvents->shouldReceive('store')->once();
    $this->webhookEvents->shouldReceive('markProcessed')->once();

    expect($this->action->execute($request))->toBe(['ok' => true]);
});

it('dispatches customer.subscription.updated → onSubscriptionChange', function () {
    $payload = [
        'id' => 'evt_2', 'type' => 'customer.subscription.updated',
        'data' => ['object' => ['id' => 'sub_x']],
    ];
    $request = Request::create('/x', 'POST', content: json_encode($payload));

    $this->helper->shouldReceive('buildEventRow')->andReturn([]);
    $this->helper->shouldReceive('onSubscriptionChange')->once()->with(['id' => 'sub_x']);
    $this->webhookEvents->shouldReceive('store')->once();
    $this->webhookEvents->shouldReceive('markProcessed')->once();

    $this->action->execute($request);
});

it('dispatches customer.subscription.deleted → onSubscriptionCancelled', function () {
    $payload = [
        'id' => 'evt_3', 'type' => 'customer.subscription.deleted',
        'data' => ['object' => ['customer' => 'cus_x']],
    ];
    $request = Request::create('/x', 'POST', content: json_encode($payload));

    $this->helper->shouldReceive('buildEventRow')->andReturn([]);
    $this->helper->shouldReceive('onSubscriptionCancelled')->once();
    $this->webhookEvents->shouldReceive('store')->once();
    $this->webhookEvents->shouldReceive('markProcessed')->once();

    $this->action->execute($request);
});

it('marks failed when handler throws', function () {
    $payload = [
        'id' => 'evt_4', 'type' => 'customer.subscription.updated',
        'data' => ['object' => []],
    ];
    $request = Request::create('/x', 'POST', content: json_encode($payload));

    $this->helper->shouldReceive('buildEventRow')->andReturn([]);
    $this->helper->shouldReceive('onSubscriptionChange')->andThrow(new \RuntimeException('x'));
    $this->webhookEvents->shouldReceive('store')->once();
    $this->webhookEvents->shouldReceive('markFailed')->once();

    expect($this->action->execute($request))->toBe(['ok' => true]);
});

it('ignores unknown event types', function () {
    $payload = ['id' => 'evt_5', 'type' => 'unknown.type', 'data' => ['object' => []]];
    $request = Request::create('/x', 'POST', content: json_encode($payload));

    $this->helper->shouldReceive('buildEventRow')->andReturn([]);
    $this->helper->shouldNotReceive('onCheckoutComplete');
    $this->helper->shouldNotReceive('onSubscriptionChange');
    $this->helper->shouldNotReceive('onSubscriptionCancelled');
    $this->webhookEvents->shouldReceive('store')->once();
    $this->webhookEvents->shouldReceive('markProcessed')->once();

    expect($this->action->execute($request))->toBe(['ok' => true]);
});
