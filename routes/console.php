<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

use Illuminate\Support\Facades\Schedule;

// Menjalankan sync WordPress setiap 30 menit (atau sesuaikan kebutuhan)
Schedule::command('sync:wordpress')->everyThirtyMinutes();
