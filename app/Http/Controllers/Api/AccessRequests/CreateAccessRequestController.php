<?php

namespace App\Http\Controllers\Api\AccessRequests;

use App\Actions\AccessRequest\CreateAccessRequestAction;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\AccessRequests\CreateAccessRequestRequest;
use Illuminate\Http\JsonResponse;

class CreateAccessRequestController extends Controller
{
    public function __construct(private readonly CreateAccessRequestAction $action) {}

    public function __invoke(CreateAccessRequestRequest $request): JsonResponse
    {
        $id = $this->action->execute(
            $request->user(),
            $request->string('requested_permission')->toString(),
            $request->input('target_url'),
        );

        return response()->json([
            'id' => $id,
            'message' => 'Request submitted. Your team admins were notified.',
        ], 201);
    }
}
