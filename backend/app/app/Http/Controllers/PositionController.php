<?php

namespace App\Http\Controllers;

use App\Actions\Positions\GetPositionsAction;
use App\Models\Position;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PositionController extends ApiController
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request, GetPositionsAction $action): JsonResponse
    {
        return $this->successResponse($action->handle($request->user()));
    }

    // @codeCoverageIgnoreStart
    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //
    }
    // @codeCoverageIgnoreEnd

    // @codeCoverageIgnoreStart
    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }
    // @codeCoverageIgnoreEnd

    // @codeCoverageIgnoreStart
    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
    }
    // @codeCoverageIgnoreEnd

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
