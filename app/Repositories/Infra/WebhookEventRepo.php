<?php

namespace App\Repositories\Infra;

use Illuminate\Support\Facades\DB;

/**
 * Idempotent append-only log of inbound webhook events. `insertOrIgnore` relies
 * on the UNIQUE(source, ep_event_id) constraint to swallow retries from EP/Stripe.
 */
class WebhookEventRepo
{
    public function store(array $data): void
    {
        DB::table('webhook_events')->insertOrIgnore(array_merge([
            'received_at' => $data['received_at'] ?? now(),
            'created_at' => now(),
            'updated_at' => now(),
        ], $data));
    }

    public function markProcessed(string $source, string $eventId): void
    {
        DB::table('webhook_events')
            ->where('source', $source)
            ->where('ep_event_id', $eventId)
            ->update(['processed_at' => now(), 'updated_at' => now()]);
    }

    public function markFailed(string $source, string $eventId, string $error): void
    {
        DB::table('webhook_events')
            ->where('source', $source)
            ->where('ep_event_id', $eventId)
            ->update(['error' => substr($error, 0, 500), 'updated_at' => now()]);
    }
}
