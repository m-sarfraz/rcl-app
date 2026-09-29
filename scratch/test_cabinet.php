<?php

require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "Total cabinet members: " . App\Models\VccCabinet::count() . PHP_EOL;
foreach (App\Models\VccCabinet::orderBy('display_order')->get() as $m) {
    echo sprintf("%2d. %-28s | %-28s | %s\n", $m->display_order, $m->name, $m->role_title, $m->photo);
}
