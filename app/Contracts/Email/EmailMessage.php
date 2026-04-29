<?php

namespace App\Contracts\Email;

/**
 * Transport-agnostic email payload. Drivers under
 * App\Mixins\Integrations\Email\* consume this DTO so the calling code
 * never depends on a specific provider's SDK shape.
 */
final class EmailMessage
{
    /**
     * @param  list<string>  $to
     * @param  list<string>  $cc
     * @param  list<string>  $bcc
     * @param  list<array{path:string,name?:string,mime?:string}>  $attachments
     * @param  array<string,string>  $headers
     */
    public function __construct(
        public array $to,
        public string $subject,
        public string $html,
        public ?string $text = null,
        public ?string $fromAddress = null,
        public ?string $fromName = null,
        public ?string $replyTo = null,
        public array $cc = [],
        public array $bcc = [],
        public array $attachments = [],
        public array $headers = [],
    ) {}

    public static function make(string|array $to, string $subject, string $html): self
    {
        return new self(
            to: is_array($to) ? array_values($to) : [$to],
            subject: $subject,
            html: $html,
        );
    }

    /** @return array<string,mixed> */
    public function toArray(): array
    {
        return [
            'to' => $this->to,
            'subject' => $this->subject,
            'html' => $this->html,
            'text' => $this->text,
            'fromAddress' => $this->fromAddress,
            'fromName' => $this->fromName,
            'replyTo' => $this->replyTo,
            'cc' => $this->cc,
            'bcc' => $this->bcc,
            'attachments' => $this->attachments,
            'headers' => $this->headers,
        ];
    }

    /** @param  array<string,mixed>  $data */
    public static function fromArray(array $data): self
    {
        return new self(
            to: (array) ($data['to'] ?? []),
            subject: (string) ($data['subject'] ?? ''),
            html: (string) ($data['html'] ?? ''),
            text: $data['text'] ?? null,
            fromAddress: $data['fromAddress'] ?? null,
            fromName: $data['fromName'] ?? null,
            replyTo: $data['replyTo'] ?? null,
            cc: (array) ($data['cc'] ?? []),
            bcc: (array) ($data['bcc'] ?? []),
            attachments: (array) ($data['attachments'] ?? []),
            headers: (array) ($data['headers'] ?? []),
        );
    }
}
