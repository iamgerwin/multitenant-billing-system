<?php

declare(strict_types=1);

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\InvoiceController;
use App\Http\Controllers\Api\OrganizationController;
use App\Http\Controllers\Api\VendorController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group.
|
*/

// Public routes
Route::prefix('auth')->name('auth.')->group(function () {
    Route::post('/login', [AuthController::class, 'login'])->name('login');
});

// Protected routes
Route::middleware('auth:sanctum')->group(function () {
    // Auth routes
    Route::prefix('auth')->name('auth.')->group(function () {
        Route::get('/user', [AuthController::class, 'user'])->name('user');
        Route::post('/logout', [AuthController::class, 'logout'])->name('logout');
        Route::post('/logout-all', [AuthController::class, 'logoutAll'])->name('logout-all');
    });

    // Routes requiring tenant context
    Route::middleware('tenant')->group(function () {
        // Organization
        Route::get('/organization', [OrganizationController::class, 'show'])
            ->name('organization.show');

        // Vendors
        Route::apiResource('vendors', VendorController::class);

        // Invoices
        Route::get('/invoices/generate-number', [InvoiceController::class, 'generateNumber'])
            ->name('invoices.generate-number');
        Route::apiResource('invoices', InvoiceController::class);
        Route::patch('/invoices/{invoice}/status', [InvoiceController::class, 'updateStatus'])
            ->name('invoices.update-status');
    });
});
