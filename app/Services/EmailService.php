<?php

namespace App\Services;

use App\Contracts\Email\EmailMessage;
use App\Contracts\Email\EmailServiceContract;
use App\Jobs\SendEmailJob;

/**
 * Front door for application code that needs to send email. Holds the
 * configured driver (resolved via the EmailServiceContract container
 * binding) and adds queueing on top — drivers themselves stay synchronous
 * and ignorant of the queue.
 *
 * Always prefer ::queue() in request-handling code; ::send() blocks on the
 * provider's HTTP call and should only be used from CLI / already-queued
 * contexts.
 */
final class EmailService
{
    public function __construct(
        private readonly EmailServiceContract $driver,
    ) {}

    public function send(EmailMessage $message): ?string
    {
        return $this->driver->send($message);
    }

    public function queue(EmailMessage $message, ?string $queueName = null): void
    {
        $job = new SendEmailJob($message->toArray());
        if ($queueName) {
            $job->onQueue($queueName);
        }
        dispatch($job);
    }

    public function later(\DateTimeInterface|\DateInterval|int $delay, EmailMessage $message, ?string $queueName = null): void
    {
        $job = new SendEmailJob($message->toArray());
        if ($queueName) {
            $job->onQueue($queueName);
        }
        dispatch($job)->delay($delay);
    }

    public function driverName(): string
    {
        return $this->driver->name();
    }
}
