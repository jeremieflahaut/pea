<?php

use App\Models\Allocation;
use App\Models\Position;
use App\Services\FinancialScraperService;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\App;

it('updates current_price of positions based on allocations using the scraper service', function () {
    Log::spy();

    $allocation = Allocation::factory()->create([
        'isin' => 'FR0000000001',
        'ticker' => 'AAPL',
    ]);

    $position = Position::factory()->create([
        'isin' => $allocation->isin,
        'user_id' => $allocation->user_id,
        'current_price' => 0,
    ]);

    $fakeService = Mockery::mock(FinancialScraperService::class);
    $fakeService->shouldReceive('getPrice')
        ->with('AAPL')
        ->andReturn(123.45);

    App::instance(FinancialScraperService::class, $fakeService);

    Artisan::call('app:get-positions-price');

    $position->refresh();

    expect($position->current_price)->toBe(123.45);
});

it('logs an error when no position is found for an allocation', function () {
    Log::spy();

    $allocation = Allocation::factory()->create([
        'isin' => 'FR0000004040',
        'ticker' => 'MISSING',
    ]);

    $fakeService = Mockery::mock(FinancialScraperService::class);
    $fakeService->shouldReceive('getPrice')->with('MISSING')->andReturn(0);
    app()->instance(FinancialScraperService::class, $fakeService);

    Artisan::call('app:get-positions-price');

    Log::shouldHaveReceived('error')
        ->once()
        ->with("Aucune position trouvée pour ISIN : {$allocation->isin}");
});
