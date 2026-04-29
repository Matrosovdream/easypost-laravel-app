<?php

namespace App\Http\Controllers\Rest\PublicApi;

use App\Actions\Contact\SubmitContactAction;
use App\Http\Controllers\Controller;
use App\Http\Requests\Rest\PublicApi\ContactRequest;
use Illuminate\Http\JsonResponse;

class ContactController extends Controller
{
    public function __construct(private readonly SubmitContactAction $action) {}

    public function __invoke(ContactRequest $request): JsonResponse
    {
        $submission = $this->action->execute($request, $request->validated());

        return response()->json([
            'message' => 'Thanks — we received your message.',
            'id' => $submission->id,
        ], 201);
    }
}
