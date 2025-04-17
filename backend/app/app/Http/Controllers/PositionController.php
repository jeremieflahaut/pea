<?php

namespace App\Http\Controllers;

use App\Models\Position;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PositionController extends ApiController
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request): JsonResponse
    {
        $positions = Position::where('user_id', $request->user()->id)
            ->get()
            ->map(function ($position) {
                return [
                    'id' => $position->id,
                    'name' => $position->name,
                    'isin' => $position->isin,
                    'quantity' => $position->quantity,
                    'average_price' => $position->average_price,
                    'current_price' => $position->current_price,
                ];
            });

        return $this->successResponse($positions);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //
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
    public function update(Request $request, string $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }
}
