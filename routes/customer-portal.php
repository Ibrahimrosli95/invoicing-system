<?php

use App\Http\Controllers\CustomerPortal\AuthController;
use App\Http\Controllers\CustomerPortal\DashboardController;
use App\Http\Controllers\CustomerPortal\QuotationController;
use App\Http\Controllers\CustomerPortal\InvoiceController;
use App\Http\Controllers\CustomerPortal\ProfileController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Customer Portal Routes
|--------------------------------------------------------------------------
|
| Here is where you can register customer portal routes for your application.
| These routes are loaded by the RouteServiceProvider within a group which
| is assigned the "customer-portal" middleware group and prefix.
|
*/

// Authentication Routes (Guest Only)
Route::middleware(['guest:customer-portal'])->group(function () {
    Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [AuthController::class, 'login']);
    
    Route::get('/register', [AuthController::class, 'showRegister'])->name('register');
    Route::post('/register', [AuthController::class, 'register']);
    
    Route::get('/forgot-password', [AuthController::class, 'showForgotPassword'])->name('password.request');
    Route::post('/forgot-password', [AuthController::class, 'sendResetLink'])->name('password.email');
    
    Route::get('/reset-password/{token}', [AuthController::class, 'showResetPassword'])->name('password.reset');
    Route::post('/reset-password', [AuthController::class, 'resetPassword'])->name('password.store');
});

// Authenticated Customer Portal Routes
Route::middleware(['customer-portal.auth'])->group(function () {
    // Authentication Actions
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');
    Route::get('/verify-email/{token}', [AuthController::class, 'verify'])->name('verification.verify');
    Route::post('/email/verification-notification', [AuthController::class, 'resendVerification'])->name('verification.send');
    
    // Dashboard
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    
    // Quotations
    Route::get('/quotations', [QuotationController::class, 'index'])->name('quotations.index');
    Route::get('/quotations/{quotation}', [QuotationController::class, 'show'])->name('quotations.show');
    Route::get('/quotations/{quotation}/pdf', [QuotationController::class, 'downloadPDF'])->name('quotations.pdf');
    Route::post('/quotations/{quotation}/accept', [QuotationController::class, 'accept'])->name('quotations.accept');
    Route::post('/quotations/{quotation}/reject', [QuotationController::class, 'reject'])->name('quotations.reject');
    
    // Invoices
    Route::get('/invoices', [InvoiceController::class, 'index'])->name('invoices.index');
    Route::get('/invoices/{invoice}', [InvoiceController::class, 'show'])->name('invoices.show');
    Route::get('/invoices/{invoice}/pdf', [InvoiceController::class, 'downloadPDF'])->name('invoices.pdf');
    
    // Payment History
    Route::get('/payments', [InvoiceController::class, 'payments'])->name('payments.index');
    
    // Profile Management
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
    Route::patch('/profile/password', [ProfileController::class, 'updatePassword'])->name('profile.password');
    Route::patch('/profile/preferences', [ProfileController::class, 'updatePreferences'])->name('profile.preferences');
});

// Redirect root to dashboard for authenticated users, login for guests
Route::get('/', function () {
    if (Auth::guard('customer-portal')->check()) {
        return redirect()->route('customer-portal.dashboard');
    }
    return redirect()->route('customer-portal.login');
})->name('home');