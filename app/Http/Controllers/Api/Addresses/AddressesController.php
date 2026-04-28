<?php

namespace App\Http\Controllers\Api\Addresses;

use App\Actions\Addresses\CreateAndVerifyAddressAction;
use App\Actions\Addresses\DeleteAddressAction;
use App\Actions\Addresses\ListAddressesAction;
use App\Actions\Addresses\ShowAddressAction;
use App\Actions\Addresses\UpdateAddressAction;
use App\Actions\Addresses\VerifyExistingAddressAction;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\Addresses\CreateAddressRequest;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AddressesController extends Controller
{
    public function __construct(
        private readonly ListAddressesAction $list,
        private readonly ShowAddressAction $show,
        private readonly CreateAndVerifyAddressAction $create,
        private readonly UpdateAddressAction $update,
        private readonly VerifyExistingAddressAction $verify,
        private readonly DeleteAddressAction $delete,
    ) {}

    public function index(Request $request): JsonResponse
    {
        return response()->json($this->list->execute(
            $request->user(),
            $request->query('q'),
            (int) $request->query('per_page', 25),
        ));
    }

    public function show(int $id): JsonResponse
    {
        return response()->json($this->show->execute($id));
    }

    public function store(CreateAddressRequest $request): JsonResponse
    {
        return response()->json($this->create->execute(
            $request->user(),
            $request->validated(),
            verify: (bool) $request->input('verify', true),
        ), 201);
    }

    public function update(CreateAddressRequest $request, int $id): JsonResponse
    {
        return response()->json($this->update->execute($id, $request->validated()));
    }

    public function verify(int $id): JsonResponse
    {
        return response()->json($this->verify->execute($id));
    }

    public function destroy(int $id): JsonResponse
    {
        return response()->json($this->delete->execute($id));
    }
}
