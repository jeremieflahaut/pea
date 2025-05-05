<?php

namespace App\Http\Controllers;

use App\Models\Position;
use App\Models\Transaction;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class SummaryController extends ApiController
{
    public function __invoke(Request $request): JsonResponse
    {
        $userId = $request->user()->id;

        $transactions = Transaction::where('user_id', $userId)->get();

        $totalDeposits = $transactions->reduce(function ($sum, $t) {
            $amount = $t->quantity * $t->price;
            return $sum + ($t->type === 'buy' ? $amount : -$amount);
        }, 0);

        $currentValue = Position::where('user_id', $userId)
            ->selectRaw('SUM(quantity * current_price) as value')
            ->value('value') ?? 0;

        $gain = $currentValue - $totalDeposits;

        return $this->successResponse([
            'total_deposits' => round($totalDeposits, 2),
            'current_value' => round($currentValue, 2),
            'total_gain' => round($gain, 2),
            'performance_percent' => $totalDeposits > 0 ? round($gain / $totalDeposits * 100, 2) : 0,
        ]);
    }
}
