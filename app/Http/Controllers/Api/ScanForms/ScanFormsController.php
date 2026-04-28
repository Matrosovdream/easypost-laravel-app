<?php

namespace App\Http\Controllers\Api\ScanForms;

use App\Actions\ScanForms\GenerateScanFormAction;
use App\Actions\ScanForms\ListScanFormsAction;
use App\Actions\ScanForms\ShowScanFormAction;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\ScanForms\CreateScanFormRequest;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ScanFormsController extends Controller
{
    public function __construct(
        private readonly ListScanFormsAction $list,
        private readonly ShowScanFormAction $show,
        private readonly GenerateScanFormAction $generate,
    ) {}

    public function index(Request $request): JsonResponse
    {
        return response()->json($this->list->execute(
            $request->user(),
            (int) $request->query('per_page', 25),
        ));
    }

    public function show(int $id): JsonResponse
    {
        return response()->json($this->show->execute($id));
    }

    public function store(CreateScanFormRequest $request): JsonResponse
    {
        try {
            return response()->json($this->generate->execute(
                $request->user(),
                $request->input('shipment_ids'),
            ), 201);
        } catch (\Symfony\Component\HttpKernel\Exception\HttpException $e) {
            throw $e;
        } catch (\RuntimeException $e) {
            return response()->json(['message' => $e->getMessage()], 422);
        }
    }
}
