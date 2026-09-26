<?php

use App\Http\Controllers\Portal\PreRegistrationController;
use Illuminate\Support\Facades\Route;

Route::get('/pre-register', [PreRegistrationController::class, 'publicCreate'])->name('public.pre-register');
Route::post('/pre-register', [PreRegistrationController::class, 'publicStore'])->name('public.pre-register.store');

Route::middleware(['auth', 'verified', 'role:patient'])->group(function () {
    Route::get('/portal/pre-register', [PreRegistrationController::class, 'create'])->name('portal.pre-register');
    Route::post('/portal/pre-register', [PreRegistrationController::class, 'store'])->name('portal.pre-register.store');
});
