<?php

use App\Http\Controllers\Portal\PreRegistrationController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth', 'verified', 'role:patient'])->group(function () {
    Route::get('/portal/pre-register', [PreRegistrationController::class, 'create'])->name('portal.pre-register');
    Route::post('/portal/pre-register', [PreRegistrationController::class, 'store'])->name('portal.pre-register.store');
});
