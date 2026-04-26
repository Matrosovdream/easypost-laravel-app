<?php

namespace App\Jobs;

use App\Contracts\Email\EmailMessage;
use App\Contracts\Email\EmailServiceContract;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable as QueueableTrait;

/**
 * Backs EmailService::queue(). Resolves the bound driver at runtime so a
 * config change between enqueue and execution still picks up the right
 * provider.
 */
class SendEmailJob implements ShouldQueue
{
    use Queueable, QueueableTrait;

    public int $tries = 3;
    public int $backoff = 60;

    /** @param  array<string,mixed>  $payload */
    public function __construct(public array $payload) {}

    public function handle(EmailServiceContract $driver): void
    {
        $driver->send(EmailMessage::fromArray($this->payload));
    }
}
