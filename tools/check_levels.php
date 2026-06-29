<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\SchoolLevel;
use App\Models\User;

echo "SchoolLevels: " . SchoolLevel::count() . PHP_EOL;
echo "Users: " . User::count() . PHP_EOL;
$levels = SchoolLevel::all()->toArray();
if (count($levels) > 0) {
    echo "First level: " . json_encode($levels[0]) . PHP_EOL;
} else {
    echo "No levels rows." . PHP_EOL;
}
