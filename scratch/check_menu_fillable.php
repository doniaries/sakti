<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

// Check if Menu has a mutator or mutator behavior
$menu = new \App\Models\Menu();
echo "Fillable: " . json_encode($menu->getFillable()) . "\n";
echo "Guarded: " . json_encode($menu->getGuarded()) . "\n";
