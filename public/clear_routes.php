<?php
echo "<pre>";
echo "Clearing route cache...\n";
echo shell_exec('php ../artisan route:clear 2>&1');
echo "Clearing cache...\n";
echo shell_exec('php ../artisan cache:clear 2>&1');
echo "Clearing view cache...\n";
echo shell_exec('php ../artisan view:clear 2>&1');
echo "Clearing config cache...\n";
echo shell_exec('php ../artisan config:clear 2>&1');
echo "Done!\n";
echo "</pre>";
