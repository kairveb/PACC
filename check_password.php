<?php
require 'vendor/autoload.php';
$app = require 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$user = App\Models\User::where('email', 'super.admin@coor.test')->first();
echo Illuminate\Support\Facades\Hash::check('Password123!', $user->password) ? 'HASH_OK' : 'HASH_FAIL';
