<?php

use App\Actions\Webhooks\HandleEasyPostWebhookAction;
use App\Actions\Webhooks\ProcessEasypostWebhookAction;
use App\Helpers\Webhooks\EasyPostWebhookHelper;
use App\Repositories\Infra\WebhookEventRepo;
use Illuminate\Http\Request;

beforeEach(function () {
    config()->set('services.easypost.webhook_secret', 'shhh');
    $this->process = mock(ProcessEasypostWebhookAction::class);
    $this->webhookEvents = mock(WebhookEventRepo::class);
    $this->helper = mock(EasyPostWebhookHelper::class);
    $this->action = new HandleEasyPostWebhookAction(
        $this->process, $this->webhookEvents, $this->helper,
    );
});

it('returns 401 + stores invalid event when signature is bad', function () {
    $body = '{"id":"evt_1","description":"tracker.updated","result":{}}';
    $request = Request::create('/x', 'POST', server: ['HTTP_X_HMAC_SIGNATURE' => 'wrong'], content: $body);

    $this->helper->shouldReceive('isValidSignature')->andReturn(false);
    $this->helper->shouldReceive('resolveTeamId')->andReturn(null);
    $this->helper->shouldReceive('buildEventRow')->andReturn(['source' => 'easypost']);

    $this->webhookEvents->shouldReceive('store')->once();
    $this->process->shouldNotReceive('execute');

    $out = $this->action->execute($request);
    expect($out['_status'])->toBe(401);
    expect($out['body']['message'])->toBe('Invalid signature.');
});

it('returns 200 + dispatches process action when signature is valid', function () {
    $body = '{"id":"evt_1","description":"tracker.updated","result":{"id":"trk_x"}}';
    $request = Request::create('/x', 'POST', server: ['HTTP_X_HMAC_SIGNATURE' => 'good'], content: $body);

    $this->helper->shouldReceive('isValidSignature')->andReturn(true);
    $this->helper->shouldReceive('resolveTeamId')->andReturn(7);
    $this->helper->shouldReceive('buildEventRow')->andReturn(['source' => 'easypost']);

    $this->webhookEvents->shouldReceive('store')->once();
    $this->webhookEvents->shouldReceive('markProcessed')->once()->with('easypost', 'evt_1');
    $this->process->shouldReceive('execute')
        ->once()
        ->withArgs(fn ($desc, $result, $teamId) =>
            $desc === 'tracker.updated'
            && $teamId === 7);

    $out = $this->action->execute($request);
    expect($out['_status'])->toBe(200);
    expect($out['body'])->toBe(['ok' => true]);
});

it('still returns 200 when downstream processing throws', function () {
    $body = '{"id":"evt_2","description":"shipment.purchased","result":{}}';
    $request = Request::create('/x', 'POST', content: $body);

    $this->helper->shouldReceive('isValidSignature')->andReturn(true);
    $this->helper->shouldReceive('resolveTeamId')->andReturn(null);
    $this->helper->shouldReceive('buildEventRow')->andReturn(['source' => 'easypost']);

    $this->webhookEvents->shouldReceive('store')->once();
    $this->webhookEvents->shouldReceive('markFailed')->once();
    $this->process->shouldReceive('execute')->andThrow(new \RuntimeException('boom'));

    $out = $this->action->execute($request);
    expect($out['_status'])->toBe(200);
});
