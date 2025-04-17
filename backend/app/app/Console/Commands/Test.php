<?php

namespace App\Console\Commands;

use App\Models\Position;
use App\Models\Transaction;
use Illuminate\Console\Command;

class Test extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:test';

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
        $position = Position::first();

        $transaction = Transaction::first();

        dd($position->average_price);
    }
}
