<?php

use App\Jobs\SendAppointmentReminders;
use App\Services\AppointmentCompletion;

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Schedule::job(new SendAppointmentReminders())->hourly();

// Von Hand: php artisan appointments:complete
Artisan::command('appointments:complete', function (AppointmentCompletion $completion) {
    $count = $completion->completeFinished();
    $this->info("{$count} Termin(e) abgeschlossen.");
})->purpose('Vergangene, bezahlte Termine abschließen und um Bewertung bitten');

// Falls auf dem Server ein Cron für schedule:run läuft
Schedule::command('appointments:complete')->everyTenMinutes()->withoutOverlapping();