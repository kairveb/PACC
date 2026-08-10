<?php
require 'vendor/autoload.php';
$app = require 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$users = App\Models\User::orderBy('id')->get(['id','email','name','password']);
foreach ($users as $u) {
    echo $u->id . '|' . $u->email . '|' . $u->name . '|' . substr($u->password, 0, 20) . PHP_EOL;
}
