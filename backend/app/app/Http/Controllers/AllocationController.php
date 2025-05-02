<?php

namespace App\Http\Controllers;

use App\Actions\Allocations\GetAllocationsAction;
use App\Http\Requests\StoreAllocationRequest;
use App\Http\Requests\UpdateAllocationRequest;
use App\Models\Allocation;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AllocationController extends ApiController
{
    public function index(Request $request, GetAllocationsAction $action): JsonResponse
    {
        return $this->successResponse($action->handle($request->user()));
    }

    public function store(StoreAllocationRequest $request): JsonResponse
    {
        $allocation = $request->user()->allocations()->create($request->validated());

        return $this->successResponse($allocation->toArray(), 201);
    }

    public function update(UpdateAllocationRequest $request, Allocation $allocation): JsonResponse
    {
        $allocation->update($request->validated());

        return $this->successResponse($allocation->toArray());
    }
}
