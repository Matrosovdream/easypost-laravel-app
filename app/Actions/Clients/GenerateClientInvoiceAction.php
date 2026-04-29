<?php

namespace App\Actions\Clients;

use App\Helpers\Clients\InvoiceHelper;
use App\Repositories\Client\ClientRepo;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Gate;

class GenerateClientInvoiceAction
{
    public function __construct(
        private readonly ClientRepo $clients,
        private readonly InvoiceHelper $invoices,
    ) {}

    /**
     * Returns an in-memory invoice draft — no `invoices` table yet (P1).
     * Aggregates delivered/purchased shipments within the window, applies
     * FlexRate markup, and returns line items plus totals.
     */
    public function execute(int $id, Carbon $from, Carbon $to): array
    {
        $client = $this->clients->getModel()->newQuery()->find($id);
        abort_if(! $client, 404);
        Gate::authorize('view', $client);

        $lines = $this->invoices->buildLines($client, $from, $to);

        return [
            'client_id'    => $client->id,
            'company_name' => $client->company_name,
            'period'       => ['from' => $from->toIso8601String(), 'to' => $to->toIso8601String()],
            'lines'        => $lines,
            'totals'       => $this->invoices->summarizeTotals($lines),
        ];
    }
}
