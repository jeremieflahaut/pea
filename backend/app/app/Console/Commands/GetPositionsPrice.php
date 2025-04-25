<?php

namespace App\Console\Commands;

use App\Models\Allocation;
use App\Models\Position;
use App\Services\FinancialScraperService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

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
    public function handle(): void
    {
        Allocation::whereNotNull('ticker')
            ->get()->each(function (Allocation $allocation) {
                $price = app(FinancialScraperService::class)->getPrice($allocation->ticker);

                $position = Position::where('isin', $allocation->isin)->first();

                if (! $position) {
                    Log::error("Aucune position trouvée pour ISIN : {$allocation->isin}");
                    return;
                }

                $position->update(['current_price' => $price]);

            });

        Log::info("Mise a jour des prix terminée");
    }
}
