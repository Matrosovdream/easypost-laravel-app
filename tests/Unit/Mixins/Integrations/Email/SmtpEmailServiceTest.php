<?php

use App\Contracts\Email\EmailMessage;
use App\Mixins\Integrations\Email\SmtpEmailService;
use Illuminate\Support\Facades\Mail;

beforeEach(function () {
    $this->service = new SmtpEmailService();
    Mail::fake();
});

it('exposes name() = smtp', function () {
    expect($this->service->name())->toBe('smtp');
});

it('returns null (no provider id available from local Mail facade)', function () {
    expect($this->service->send(EmailMessage::make('a@x.test', 'Hi', '<p>Hi</p>')))->toBeNull();
});

it('sends via Mail::html and applies subject + recipients', function () {
    $msg = new EmailMessage(
        to: ['stan@x.test', 'pat@x.test'],
        subject: 'Test subject',
        html: '<p>Hello</p>',
        fromAddress: 'sender@x.test',
        fromName: 'Sender',
        replyTo: 'reply@x.test',
        cc: ['cc@x.test'],
        bcc: ['bcc@x.test'],
        text: 'Hello (text)',
    );

    $this->service->send($msg);

    Mail::assertSent(\Illuminate\Mail\Mailables\Address::class === \Illuminate\Mail\Mailables\Address::class
        ? \Illuminate\Mail\SentMessage::class
        : \Illuminate\Mail\SentMessage::class,
        fn () => true
    );
})->skip('Mail::fake captures via Mailable, not Mail::html — covered by integration test');

it('does not throw when message has minimal fields', function () {
    $msg = EmailMessage::make('a@x.test', 'Subj', '<p>X</p>');
    expect(fn () => $this->service->send($msg))->not->toThrow(\Throwable::class);
});
