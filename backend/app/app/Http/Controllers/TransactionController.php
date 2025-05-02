<?php

namespace App\Http\Controllers;

use App\Actions\Transactions\StoreTransactionAction;
use App\Http\Requests\TransactionRequest;
use App\Models\Transaction;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response as ResponseAlias;

class TransactionController extends ApiController
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request): JsonResponse
    {
        return $this->successResponse($request->user()->transactions()->latest()->get());
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(TransactionRequest $request, StoreTransactionAction $action): JsonResponse
    {
        $transaction = $action->handle($request->user(), $request->validated());

        return $this->successResponse($transaction->toArray(), ResponseAlias::HTTP_CREATED);
    }

    // @codeCoverageIgnoreStart
    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }
    // @codeCoverageIgnoreEnd

    /**
     * Update the specified resource in storage.
     */
    public function update(TransactionRequest $request, Transaction $transaction): JsonResponse
    {
        abort_if($transaction->user_id !== $request->user()->id, 403);

        $transaction->update($request->validated());

        return $this->successResponse($transaction->toArray());
    }

    // @codeCoverageIgnoreStart
    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }
    // @codeCoverageIgnoreEnd
}
