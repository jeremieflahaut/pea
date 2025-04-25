<?php

use App\Console\Commands\GetPositionsPrice;
use Illuminate\Support\Facades\Schedule;


Schedule::command(GetPositionsPrice::class)->everySixHours();


