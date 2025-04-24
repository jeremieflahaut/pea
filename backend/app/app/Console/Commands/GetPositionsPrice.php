<?php

namespace App\Console\Commands;

use App\Services\FinancialScraperService;
use Illuminate\Console\Command;

class GetPositionsPrice extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:get-positions-price';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $price = app(FinancialScraperService::class)->getPrice('PCEU.PA');

        dd($price);


    }
}
