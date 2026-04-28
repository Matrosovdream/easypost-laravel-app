<?php

namespace App\Http\Controllers\Api\Reports;

use App\Actions\Reports\CreateReportAction;
use App\Actions\Reports\ListReportsAction;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\Reports\CreateReportRequest;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ReportsController extends Controller
{
    public function __construct(
        private readonly ListReportsAction $list,
        private readonly CreateReportAction $create,
    ) {}

    public function index(Request $request): JsonResponse
    {
        return response()->json($this->list->execute($request->user()));
    }

    public function store(CreateReportRequest $request): JsonResponse
    {
        return response()->json($this->create->execute($request->user(), $request->validated()), 201);
    }
}
