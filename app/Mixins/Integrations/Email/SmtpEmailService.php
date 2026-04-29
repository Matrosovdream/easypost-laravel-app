<?php

namespace App\Mixins\Integrations\Email;

use App\Contracts\Email\EmailMessage;
use App\Contracts\Email\EmailServiceContract;
use Illuminate\Mail\Message;
use Illuminate\Support\Facades\Mail;

/**
 * Default driver — delegates to Laravel's Mail facade so it works with
 * whatever transport (smtp, ses, sendmail, log, …) is configured in
 * config/mail.php. Useful as the local/testing fallback.
 */
final class SmtpEmailService implements EmailServiceContract
{
    public function send(EmailMessage $message): ?string
    {
        Mail::html($message->html, function (Message $mail) use ($message): void {
            $mail->to($message->to)
                ->subject($message->subject);

            if ($message->fromAddress) {
                $mail->from($message->fromAddress, $message->fromName);
            }
            if ($message->replyTo) {
                $mail->replyTo($message->replyTo);
            }
            if ($message->cc) {
                $mail->cc($message->cc);
            }
            if ($message->bcc) {
                $mail->bcc($message->bcc);
            }
            if ($message->text) {
                $mail->text($message->text);
            }
            foreach ($message->attachments as $att) {
                $mail->attach($att['path'], [
                    'as' => $att['name'] ?? null,
                    'mime' => $att['mime'] ?? null,
                ]);
            }
            foreach ($message->headers as $key => $value) {
                $mail->getHeaders()->addTextHeader($key, $value);
            }
        });

        return null;
    }

    public function name(): string
    {
        return 'smtp';
    }
}
