<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

$tables = Illuminate\Support\Facades\DB::select('SHOW TABLES');
print_r($tables);
