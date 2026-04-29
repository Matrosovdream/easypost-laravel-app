<?php

namespace App\Contracts\Email;

/**
 * Driver contract every mail provider (SMTP, Mailgun, …) must implement.
 *
 * Implementations live under App\Mixins\Integrations\Email\* and are bound
 * to this interface in ServicesServiceProvider based on the configured
 * driver. Application code should depend on App\Services\EmailService
 * (which fronts queue support), not on this contract directly.
 */
interface EmailServiceContract
{
    /**
     * Send the message immediately and return the provider's message id
     * (or null if the driver doesn't expose one).
     */
    public function send(EmailMessage $message): ?string;

    /**
     * Stable identifier for the underlying driver — used in logs and to
     * pick transports at runtime.
     */
    public function name(): string;
}
