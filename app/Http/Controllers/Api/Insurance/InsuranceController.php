<?php

namespace App\Http\Controllers\Api\Insurance;

use App\Actions\Insurance\CreateStandaloneInsuranceAction;
use App\Actions\Insurance\ListInsurancesAction;
use App\Helpers\Insurance\InsuranceHelper;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\Insurance\CreateInsuranceRequest;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class InsuranceController extends Controller
{
    public function __construct(
        private readonly ListInsurancesAction $list,
        private readonly CreateStandaloneInsuranceAction $create,
        private readonly InsuranceHelper $helper,
    ) {}

    public function index(Request $request): JsonResponse
    {
        return response()->json($this->list->execute(
            $request->user(),
            (int) $request->query('per_page', 25),
        ));
    }

    public function store(CreateInsuranceRequest $request): JsonResponse
    {
        $insurance = $this->create->execute($request->user(), $request->validated());
        return response()->json($this->helper->toCreatedPayload($insurance), 201);
    }
}
