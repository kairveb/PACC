<?php
require 'vendor/autoload.php';
$app = require 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$users = App\Models\User::all();
foreach ($users as $u) {
    echo $u->email . '|' . $u->name . PHP_EOL;
}

$attempt = Illuminate\Support\Facades\Auth::attempt([
    'email' => 'super.admin@coor.test',
    'password' => 'Password123!',
]);

echo 'ATTEMPT=' . ($attempt ? 'true' : 'false') . PHP_EOL;
