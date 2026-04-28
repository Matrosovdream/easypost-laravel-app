<?php

use App\Contracts\Email\EmailMessage;
use App\Mixins\Integrations\Email\MailgunEmailService;
use Illuminate\Support\Facades\Http;

beforeEach(function () {
    $this->service = new MailgunEmailService(
        apiKey: 'key-test',
        domain: 'mg.example.test',
        defaultFromAddress: 'sender@example.test',
        defaultFromName: 'Sender',
    );
});

it('exposes name() = mailgun', function () {
    expect($this->service->name())->toBe('mailgun');
});

it('returns the message id from Mailgun on success', function () {
    Http::fake(['*/mg.example.test/messages' => Http::response(['id' => '<abc@mg>', 'message' => 'Queued'])]);

    $msg = EmailMessage::make('stan@x.test', 'Hi', '<p>Hi</p>');
    expect($this->service->send($msg))->toBe('<abc@mg>');
});

it('formats from header with name when provided', function () {
    Http::fake(['*' => Http::response(['id' => 'x'])]);

    $this->service->send(new EmailMessage(
        to: ['stan@x.test'], subject: 'Hi', html: '<p>Hi</p>',
        fromAddress: 'overrider@x.test', fromName: 'Override',
    ));

    Http::assertSent(function ($req) {
        $payload = collect($req->data())->keyBy('name')->map->contents->all();
        return ($payload['from'] ?? null) === 'Override <overrider@x.test>';
    });
});

it('falls back to default from address when message has no override', function () {
    Http::fake(['*' => Http::response(['id' => 'x'])]);

    $this->service->send(EmailMessage::make('stan@x.test', 'Hi', '<p>Hi</p>'));

    Http::assertSent(function ($req) {
        $payload = collect($req->data())->keyBy('name')->map->contents->all();
        return ($payload['from'] ?? null) === 'Sender <sender@example.test>';
    });
});

it('joins multiple recipients into a single comma-separated string', function () {
    Http::fake(['*' => Http::response(['id' => 'x'])]);

    $this->service->send(EmailMessage::make(['a@x.test', 'b@x.test'], 'Hi', '<p>Hi</p>'));

    Http::assertSent(function ($req) {
        $payload = collect($req->data())->keyBy('name')->map->contents->all();
        return ($payload['to'] ?? null) === 'a@x.test,b@x.test';
    });
});

it('throws on 4xx', function () {
    Http::fake(['*' => Http::response(['message' => 'bad'], 401)]);

    expect(fn () => $this->service->send(EmailMessage::make('a@x.test', 'Hi', '<p>Hi</p>')))
        ->toThrow(\Illuminate\Http\Client\RequestException::class);
});
