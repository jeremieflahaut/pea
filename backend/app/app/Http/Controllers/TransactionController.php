<?php

namespace App\Http\Controllers;

use App\Models\Allocation;
use App\Models\Position;
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
    public function store(Request $request): JsonResponse
    {
        $data = $request->validate([
            'isin' => 'required|string|size:12',
            'quantity' => 'required|numeric|min:0.0001',
            'price' => 'required|numeric|min:0',
            'type' => 'required|in:buy,sell',
            'date' => 'required|date',
        ]);

        $transaction = $request->user()->transactions()->create($data);

        $allocation = Allocation::where('user_id', $request->user()->id)
            ->where('isin', $data['isin'])
            ->first();

        $position = Position::firstOrNew([
            'user_id' => $request->user()->id,
            'isin' => $data['isin'],
            'name' => $allocation->name ?? $data['isin']
        ]);

        if ($data['type'] === 'buy') {
            $position->quantity = ($position->quantity ?? 0) + $data['quantity'];

        } elseif ($data['type'] === 'sell') {
            $position->quantity = max(0, ($position->quantity ?? 0) - $data['quantity']);
        }

        $position->save();

        return $this->successResponse($transaction->toArray(), ResponseAlias::HTTP_CREATED);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Transaction $transaction): JsonResponse
    {
        abort_if($transaction->user_id !== $request->user()->id, 403);

        $data = $request->validate([
            'isin'     => ['sometimes', 'required', 'string'],
            'quantity' => ['sometimes', 'required', 'numeric', 'min:0.0001'],
            'price'    => ['sometimes', 'required', 'numeric', 'min:0'],
            'type'     => ['sometimes', 'required', 'in:buy,sell'],
            'date'     => ['sometimes', 'required', 'date'],
        ]);

        $transaction->update($data);

        return $this->successResponse($transaction->toArray());
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }
}
