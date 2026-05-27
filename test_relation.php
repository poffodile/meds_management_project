<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$shifts = \App\Models\ScheduledShift::whereNotNull('staff_id')
    ->with('staff')
    ->limit(5)
    ->get();

foreach($shifts as $s) {
    echo "ID: " . $s->id . " | staff_id: " . $s->staff_id . " | staff?: " . ($s->staff ? 'Yes' : 'No') . "\n";
}
