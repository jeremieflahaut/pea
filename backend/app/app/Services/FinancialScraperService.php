<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;

class FinancialScraperService
{
    protected string $baseUrl;

    public function __construct()
    {
        $this->baseUrl = config('services.scraper.url');
    }

    public function getPrice(string $ticker): float
    {
        $result = $this->request('get', '/price', ['ticker' => $ticker]);

        return $result['price'] ?? 0;
    }

    protected function request(string $method, string $endpoint, array $query = []): ?array
    {
        $url = rtrim($this->baseUrl, '/') . '/' . ltrim($endpoint, '/');

        $response = Http::{$method}($url, $query);

        if (! $response->successful()) {
            return null;
        }

        return $response->json();
    }


}
