<?php

namespace App\Actions\Clients;

use App\Helpers\Clients\InvoiceHelper;
use App\Models\Client;
use Illuminate\Support\Carbon;

class GenerateClientInvoiceAction
{
    public function __construct(
        private readonly InvoiceHelper $invoices,
    ) {}

    /**
     * Returns an in-memory invoice draft — no `invoices` table yet (P1).
     * Aggregates delivered/purchased shipments within the window, applies
     * FlexRate markup, and returns line items plus totals.
     */
    public function execute(Client $client, Carbon $from, Carbon $to): array
    {
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
