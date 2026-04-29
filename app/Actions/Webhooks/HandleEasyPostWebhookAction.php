<?php

namespace App\Actions\Webhooks;

use App\Helpers\Webhooks\EasyPostWebhookHelper;
use App\Repositories\Infra\WebhookEventRepo;
use Illuminate\Http\Request;

class HandleEasyPostWebhookAction
{
    public function __construct(
        private readonly ProcessEasypostWebhookAction $process,
        private readonly WebhookEventRepo $webhookEvents,
        private readonly EasyPostWebhookHelper $helper,
    ) {}

    /**
     * Returns ['_status' => int, 'body' => array] for the controller to emit.
     */
    public function execute(Request $request): array
    {
        $signature = $request->header('X-Hmac-Signature', '');
        $raw = $request->getContent();

        $valid = $this->helper->isValidSignature($raw, (string) $signature);

        $payload = $request->json()->all() ?: json_decode($raw, true) ?? [];
        $eventId = (string) ($payload['id'] ?? '');
        $description = (string) ($payload['description'] ?? 'unknown');
        $result = $payload['result'] ?? [];
        $teamId = $this->helper->resolveTeamId($result);

        if (! $valid) {
            $this->webhookEvents->store($this->helper->buildEventRow(
                $teamId,
                $eventId ?: 'unsigned_'.uniqid(),
                $description,
                false,
                $payload,
                'Bad signature',
            ));
            return ['_status' => 401, 'body' => ['message' => 'Invalid signature.']];
        }

        $this->webhookEvents->store($this->helper->buildEventRow(
            $teamId,
            $eventId,
            $description,
            true,
            $payload,
        ));

        try {
            $this->process->execute($description, $result, $teamId);
            $this->webhookEvents->markProcessed('easypost', $eventId);
        } catch (\Throwable $e) {
            $this->webhookEvents->markFailed('easypost', $eventId, $e->getMessage());
            // Always 200 on valid-signature webhooks so EP doesn't retry forever
        }

        return ['_status' => 200, 'body' => ['ok' => true]];
    }
}
