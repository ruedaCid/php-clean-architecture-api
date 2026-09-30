<?php

declare(strict_types=1);

use App\Http\Controllers\CustomerController;
use Illuminate\Support\Facades\Route;

Route::post('/customers', [CustomerController::class, 'store']);
Route::get('/customers/{id}', [CustomerController::class, 'show']);
