<?php

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response as ResponseAlias;
use function PHPUnit\Framework\isNull;

abstract class ApiController
{
    public function successResponse(mixed $data, int $code = ResponseAlias::HTTP_OK): JsonResponse
    {
        return response()->json($data, $code);
    }

    public function successResponseNoContent(): JsonResponse
    {
        return response()->json(status: ResponseAlias::HTTP_NO_CONTENT);
    }

    public function errorResponse(string $message): JsonResponse
    {
        return response()->json(['error' => $message], ResponseAlias::HTTP_BAD_REQUEST);
    }
}
