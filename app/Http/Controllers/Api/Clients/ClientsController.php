<?php

namespace App\Http\Controllers\Api\Clients;

use App\Actions\Clients\CreateClientAction;
use App\Actions\Clients\GenerateClientInvoiceAction;
use App\Actions\Clients\SetClientFlexRateAction;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\Clients\CreateClientRequest;
use App\Models\Client;
use App\Repositories\Client\ClientRepo;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;

class ClientsController extends Controller
{
    public function __construct(
        private readonly CreateClientAction $create,
        private readonly SetClientFlexRateAction $setFlex,
        private readonly GenerateClientInvoiceAction $invoice,
        private readonly ClientRepo $clients,
    ) {}

    public function index(Request $request): JsonResponse
    {
        $this->authorize('viewAny', Client::class);

        $rows = $this->clients->forTeam((int) $request->user()->current_team_id);

        return response()->json([
            'data' => $rows->map(fn (Client $c) => $this->map($c))->values(),
        ]);
    }

    public function show(Request $request, int $id): JsonResponse
    {
        $client = $this->clients->getModel()->newQuery()->find($id);
        abort_if(! $client, 404);
        $this->authorize('view', $client);
        return response()->json($this->map($client));
    }

    public function store(CreateClientRequest $request): JsonResponse
    {
        $client = $this->create->execute($request->user(), $request->validated());
        return response()->json($this->map($client), 201);
    }

    public function update(CreateClientRequest $request, int $id): JsonResponse
    {
        $client = $this->clients->getModel()->newQuery()->find($id);
        abort_if(! $client, 404);
        $this->authorize('update', $client);

        $client = $this->clients->updateAttributes($client->id, $request->validated());
        return response()->json($this->map($client));
    }

    public function flexRate(Request $request, int $id): JsonResponse
    {
        $client = $this->clients->getModel()->newQuery()->find($id);
        abort_if(! $client, 404);
        $this->authorize('update', $client);

        $v = $request->validate([
            'flexrate_markup_pct' => ['required', 'numeric', 'min:0', 'max:100'],
            'per_service_markups' => ['nullable', 'array'],
        ]);

        $client = $this->setFlex->execute($client, (float) $v['flexrate_markup_pct'], $v['per_service_markups'] ?? null);
        return response()->json($this->map($client));
    }

    public function invoice(Request $request, int $id): JsonResponse
    {
        $client = $this->clients->getModel()->newQuery()->find($id);
        abort_if(! $client, 404);
        $this->authorize('view', $client);

        $v = $request->validate([
            'from' => ['required', 'date'],
            'to' => ['required', 'date', 'after_or_equal:from'],
        ]);

        return response()->json($this->invoice->execute($client, Carbon::parse($v['from']), Carbon::parse($v['to'])));
    }

    private function map(Client $c): array
    {
        return [
            'id' => $c->id,
            'company_name' => $c->company_name,
            'contact_name' => $c->contact_name,
            'contact_email' => $c->contact_email,
            'contact_phone' => $c->contact_phone,
            'flexrate_markup_pct' => (float) $c->flexrate_markup_pct,
            'per_service_markups' => $c->per_service_markups,
            'billing_mode' => $c->billing_mode,
            'credit_terms_days' => $c->credit_terms_days,
            'status' => $c->status,
            'ep_endshipper_id' => $c->ep_endshipper_id,
            'notes' => $c->notes,
            'created_at' => $c->created_at?->toIso8601String(),
        ];
    }
}
