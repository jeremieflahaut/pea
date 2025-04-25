<?php

namespace App\Http\Controllers;

use App\Models\Allocation;
use App\Models\Transaction;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Symfony\Component\HttpFoundation\Response as ResponseAlias;

class AllocationController extends ApiController
{
    public function index(Request $request): JsonResponse
    {
        $user = $request->user();

        $targets = Allocation::where('user_id', $user->id)->get();
        $transactions = Transaction::where('user_id', $user->id)->get();

        $positions = $transactions->groupBy('isin')->map(function ($txs, $isin) {
            $quantity = $txs->sum(fn($t) => $t->type === 'buy' ? $t->quantity : -$t->quantity);

            $current_price = $txs->last()?->price ?? 0;

            return [
                'isin' => $isin,
                'quantity' => $quantity,
                'current_price' => $current_price,
                'current_amount' => round($quantity * $current_price, 2),
            ];
        });

        $allocations = $targets->map(function ($target) use ($positions) {
            $position = $positions[$target->isin] ?? [
                'quantity' => 0,
                'current_price' => 0,
                'current_amount' => 0,
            ];

            return [
                'label' => $target->name,
                'isin' => $target->isin,
                'type' => $target->type,
                'target_percent' => $target->target_percent,
                'current_amount' => $position['current_amount'],
            ];
        });

        $total = $allocations->sum('current_amount');

        $allocations = $allocations->map(function ($allocation) use ($total) {
            $target_amount = round($allocation['target_percent'] / 100 * $total, 2);
            $amount_to_add = round($target_amount - $allocation['current_amount'], 2);
            $current_percent = $total > 0 ? round($allocation['current_amount'] / $total * 100, 2) : 0;

            return [
                ...$allocation,
                'current_percent' => $current_percent,
                'amount_to_add' => max($amount_to_add, 0),
            ];
        });

        return $this->successResponse($allocations->sortByDesc('target_percent')->values());
    }

    public function store(Request $request): JsonResponse
    {
        $data = $request->validate([
            'isin' => ['required', 'string', Rule::unique('allocations', 'isin')->where('user_id', $request->user()->id),],
            'name' => ['required', 'string', 'unique:App\Models\Allocation,name'], //TODO unique by user
            'ticker' => ['required', 'string', 'unique:App\Models\Allocation,ticker'], //TODO unique by user
            'type' => ['required', 'in:ETF,Action'],
            'target_percent' => ['required', 'numeric', 'min:0', 'max:100'],
        ]);

        $allocation = $request->user()->allocations()->create($data);

        return $this->successResponse($allocation->toArray(), 201);
    }

    public function update(Request $request, Allocation $allocationTarget): JsonResponse
    {
        abort_if($allocationTarget->user_id !== $request->user()->id, ResponseAlias::HTTP_FORBIDDEN);

        $data = $request->validate([
            'name' => [
                'sometimes',
                'required',
                'string',
                Rule::unique('allocations')
                    ->where('user_id', $request->user()->id)
                    ->ignore($allocationTarget->id),
            ],
            'type' => ['sometimes', 'required|in:ETF,Action'],
            'target_percent' => ['sometimes', 'required', 'numeric', 'min:0|max:100'],
        ]);

        $allocationTarget->update($data);

        return $this->successResponse($allocationTarget->toArray());
    }
}
