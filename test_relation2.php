<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$homeId = 1; // Assuming homeId is 1

$shifts = \App\Models\ScheduledShift::where('home_id', $homeId)
    ->with('staff')
    ->get()
    ->sortByDesc('start_date')
    ->map(function ($shift) {
        $actualDuration = 0;
        $shift->login_activities = collect();
        return $shift;
    });

foreach($shifts as $s) {
    if ($s->staff_id && !$s->staff) {
        echo "FAIL: Shift " . $s->id . " has staff_id " . $s->staff_id . " but staff is null\n";
    }
}
echo "Done\n";
