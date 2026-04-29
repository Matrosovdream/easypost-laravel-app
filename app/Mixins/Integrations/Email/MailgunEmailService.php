<?php

namespace App\Mixins\Integrations\Email;

use App\Contracts\Email\EmailMessage;
use App\Contracts\Email\EmailServiceContract;
use Illuminate\Http\Client\PendingRequest;
use Illuminate\Support\Facades\Http;

/**
 * Mailgun HTTP API driver. Talks directly to /messages so we don't have to
 * depend on symfony/mailgun-mailer. Returns Mailgun's message id on success.
 */
final class MailgunEmailService implements EmailServiceContract
{
    public function __construct(
        private readonly string $apiKey,
        private readonly string $domain,
        private readonly string $defaultFromAddress,
        private readonly string $defaultFromName,
        private readonly string $endpoint = 'https://api.mailgun.net/v3',
    ) {}

    public function send(EmailMessage $message): ?string
    {
        $fromAddress = $message->fromAddress ?: $this->defaultFromAddress;
        $fromName = $message->fromName ?: $this->defaultFromName;

        $payload = [
            'from' => $fromName ? \sprintf('%s <%s>', $fromName, $fromAddress) : $fromAddress,
            'to' => implode(',', $message->to),
            'subject' => $message->subject,
            'html' => $message->html,
        ];
        if ($message->text !== null) {
            $payload['text'] = $message->text;
        }
        if ($message->cc) {
            $payload['cc'] = implode(',', $message->cc);
        }
        if ($message->bcc) {
            $payload['bcc'] = implode(',', $message->bcc);
        }
        if ($message->replyTo) {
            $payload['h:Reply-To'] = $message->replyTo;
        }
        foreach ($message->headers as $key => $value) {
            $payload["h:{$key}"] = $value;
        }

        $request = $this->request()->asMultipart();
        foreach ($message->attachments as $att) {
            $request = $request->attach(
                'attachment',
                file_get_contents($att['path']),
                $att['name'] ?? basename($att['path']),
            );
        }

        $response = $request
            ->post("/{$this->domain}/messages", $payload)
            ->throw();

        return $response->json('id');
    }

    public function name(): string
    {
        return 'mailgun';
    }

    private function request(): PendingRequest
    {
        return Http::baseUrl($this->endpoint)
            ->withBasicAuth('api', $this->apiKey)
            ->acceptJson()
            ->timeout(15)
            ->retry(2, 250, throw: false);
    }
}
