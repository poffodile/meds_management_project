<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Services\Staff\StaffService;
use App\User;
use Illuminate\Support\Facades\Auth;

// Mock user
$user = User::where('is_deleted', 0)->first();
Auth::login($user);

$service = new StaffService();
$clientId = 180;
$_GET['start_date'] = '2026-04-17';
$_GET['start_time'] = '09:00';
$_GET['end_time'] = '17:00';

try {
    $result = $service->getShiftUser($clientId);
    echo "SUCCESS: Found " . count($result) . " staff members.\n";
} catch (\Exception $e) {
    echo "ERROR: " . $e->getMessage() . "\n";
}
