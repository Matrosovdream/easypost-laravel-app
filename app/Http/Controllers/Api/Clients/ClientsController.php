<?php

namespace App\Http\Controllers\Api\Clients;

use App\Actions\Clients\CreateClientAction;
use App\Actions\Clients\GenerateClientInvoiceAction;
use App\Actions\Clients\ListClientsAction;
use App\Actions\Clients\SetClientFlexRateAction;
use App\Actions\Clients\ShowClientAction;
use App\Actions\Clients\UpdateClientAction;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\Clients\CreateClientRequest;
use App\Http\Requests\Api\Clients\InvoiceRequest;
use App\Http\Requests\Api\Clients\SetFlexRateRequest;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;

class ClientsController extends Controller
{
    public function __construct(
        private readonly ListClientsAction $list,
        private readonly ShowClientAction $show,
        private readonly CreateClientAction $create,
        private readonly UpdateClientAction $update,
        private readonly SetClientFlexRateAction $setFlex,
        private readonly GenerateClientInvoiceAction $invoice,
    ) {}

    public function index(Request $request): JsonResponse
    {
        return response()->json($this->list->execute($request->user()));
    }

    public function show(int $id): JsonResponse
    {
        return response()->json($this->show->execute($id));
    }

    public function store(CreateClientRequest $request): JsonResponse
    {
        return response()->json($this->create->execute($request->user(), $request->validated()), 201);
    }

    public function update(CreateClientRequest $request, int $id): JsonResponse
    {
        return response()->json($this->update->execute($id, $request->validated()));
    }

    public function flexRate(SetFlexRateRequest $request, int $id): JsonResponse
    {
        $v = $request->validated();
        return response()->json($this->setFlex->execute(
            $id,
            (float) $v['flexrate_markup_pct'],
            $v['per_service_markups'] ?? null,
        ));
    }

    public function invoice(InvoiceRequest $request, int $id): JsonResponse
    {
        $v = $request->validated();
        return response()->json($this->invoice->execute(
            $id,
            Carbon::parse($v['from']),
            Carbon::parse($v['to']),
        ));
    }
}
