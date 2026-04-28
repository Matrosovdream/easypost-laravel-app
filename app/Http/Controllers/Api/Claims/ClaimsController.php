<?php

namespace App\Http\Controllers\Api\Claims;

use App\Actions\Claims\ApproveClaimAction;
use App\Actions\Claims\CloseClaimAction;
use App\Actions\Claims\ListClaimsAction;
use App\Actions\Claims\MarkClaimPaidAction;
use App\Actions\Claims\OpenClaimAction;
use App\Actions\Claims\ShowClaimAction;
use App\Actions\Claims\SubmitClaimAction;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\Claims\OpenClaimRequest;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ClaimsController extends Controller
{
    public function __construct(
        private readonly ListClaimsAction $list,
        private readonly ShowClaimAction $show,
        private readonly OpenClaimAction $open,
        private readonly SubmitClaimAction $submit,
        private readonly ApproveClaimAction $approve,
        private readonly MarkClaimPaidAction $pay,
        private readonly CloseClaimAction $close,
    ) {}

    public function index(Request $request): JsonResponse
    {
        return response()->json($this->list->execute(
            $request->user(),
            $request->query('state'),
            (int) $request->query('per_page', 25),
        ));
    }

    public function show(Request $request, int $id): JsonResponse
    {
        return response()->json($this->show->execute($request->user(), $id));
    }

    public function store(OpenClaimRequest $request): JsonResponse
    {
        try {
            return response()->json($this->open->execute($request->user(), $request->validated()), 201);
        } catch (\Symfony\Component\HttpKernel\Exception\HttpException $e) {
            throw $e;
        } catch (\RuntimeException $e) {
            return response()->json(['message' => $e->getMessage()], 422);
        }
    }

    public function submit(Request $request, int $id): JsonResponse
    {
        try {
            return response()->json($this->submit->execute($request->user(), $id));
        } catch (\Symfony\Component\HttpKernel\Exception\HttpException $e) {
            throw $e;
        } catch (\RuntimeException $e) {
            return response()->json(['message' => $e->getMessage()], 422);
        }
    }

    public function approve(Request $request, int $id): JsonResponse
    {
        $request->validate(['recovered_cents' => ['nullable', 'integer', 'min:0']]);

        try {
            return response()->json($this->approve->execute(
                $request->user(),
                $id,
                $request->integer('recovered_cents') ?: null,
            ));
        } catch (\Symfony\Component\HttpKernel\Exception\HttpException $e) {
            throw $e;
        } catch (\RuntimeException $e) {
            return response()->json(['message' => $e->getMessage()], 422);
        }
    }

    public function pay(Request $request, int $id): JsonResponse
    {
        $request->validate(['recovered_cents' => ['nullable', 'integer', 'min:0']]);

        try {
            return response()->json($this->pay->execute(
                $request->user(),
                $id,
                $request->integer('recovered_cents') ?: null,
            ));
        } catch (\Symfony\Component\HttpKernel\Exception\HttpException $e) {
            throw $e;
        } catch (\RuntimeException $e) {
            return response()->json(['message' => $e->getMessage()], 422);
        }
    }

    public function close(Request $request, int $id): JsonResponse
    {
        $request->validate(['reason' => ['nullable', 'string', 'max:255']]);

        return response()->json($this->close->execute($request->user(), $id, $request->input('reason')));
    }
}
