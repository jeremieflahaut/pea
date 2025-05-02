<?php

use Illuminate\Http\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use App\Http\Controllers\ApiController;

it('returns a success response with data and status', function () {
    $controller = new class extends ApiController {};

    $response = $controller->successResponse(['foo' => 'bar'], Response::HTTP_CREATED);

    expect($response)->toBeInstanceOf(JsonResponse::class)
        ->and($response->getStatusCode())->toBe(Response::HTTP_CREATED)
        ->and($response->getData(true))->toBe(['foo' => 'bar']);
});

it('returns a no content response', function () {
    $controller = new class extends ApiController {};

    $response = $controller->successResponseNoContent();

    expect($response)->toBeInstanceOf(JsonResponse::class)
        ->and($response->getStatusCode())->toBe(Response::HTTP_NO_CONTENT)
        ->and($response->getContent())->toBe('[]');
});

it('returns an error response with message', function () {
    $controller = new class extends ApiController {};

    $response = $controller->errorResponse('Something went wrong');

    expect($response)->toBeInstanceOf(JsonResponse::class)
        ->and($response->getStatusCode())->toBe(Response::HTTP_BAD_REQUEST)
        ->and($response->getData(true))->toBe(['error' => 'Something went wrong']);
});
