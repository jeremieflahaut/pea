<?php

use App\Services\FinancialScraperService;
use Illuminate\Support\Facades\Http;

it('returns the price from the API', function () {
    Http::fake([
        'https://fake-scraper.test/price?ticker=AAPL' => Http::response([
            'ticker' => 'AAPL',
            'price' => 123.45,
        ], 200),
    ]);

    config()->set('services.scraper.url', 'https://fake-scraper.test');

    $service = new FinancialScraperService();
    $price = $service->getPrice('AAPL');

    expect($price)->toBe(123.45);
});

it('returns 0 when the API returns an error response', function () {
    Http::fake([
        'https://fake-scraper.test/price?ticker=FAIL' => Http::response([], 500),
    ]);

    config()->set('services.scraper.url', 'https://fake-scraper.test');

    $service = new FinancialScraperService();
    $price = $service->getPrice('FAIL');

    expect($price)->toBe(0.0);
});
