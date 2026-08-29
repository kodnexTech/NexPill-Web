<?php

use App\Http\Controllers\Admin\AdminAuthController;
use App\Http\Controllers\Admin\AdminAuditController;
use App\Http\Controllers\Admin\AdminController;
use App\Http\Controllers\Admin\AdminDoseLogsController;
use App\Http\Controllers\Admin\AdminLegalController;
use App\Http\Controllers\Admin\AdminMedicinesController;
use App\Http\Controllers\Admin\AdminNotificationsController;
use App\Http\Controllers\Admin\AdminPlansController;
use App\Http\Controllers\Admin\AdminSubscriptionsController;
use App\Http\Controllers\Admin\AdminSupportController;
use App\Http\Controllers\Admin\AdminUsersController;
use App\Http\Middleware\AdminMiddleware;
use App\Models\LegalDocument;
use App\Models\SupportTicket;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::view('/', 'home')->name('home');
Route::view('/privacy', 'privacy')->name('privacy');
Route::view('/terms', 'terms')->name('terms');
Route::view('/data-deletion', 'data-deletion')->name('data-deletion');
Route::view('/support', 'support')->name('support');
Route::post('/support', function (Request $request) {
    $data = $request->validate([
        'name' => ['required', 'string', 'max:120'], 'email' => ['required', 'email:rfc', 'max:191'],
        'subject' => ['required', 'string', 'max:191'], 'category' => ['required', 'in:general,account,billing,technical,safety'],
        'message' => ['required', 'string', 'max:10000'],
    ]);
    $ticket = SupportTicket::create(['email' => $data['email'], 'subject' => $data['subject'], 'category' => $data['category'], 'status' => 'open']);
    $ticket->messages()->create(['message' => "From {$data['name']}:\n\n{$data['message']}"]);

    return back()->with('success', 'Thanks — your support request has been received.');
})->middleware('throttle:5,1')->name('support.store');

Route::get('/legal/{type}', function (string $type) {
    $document = LegalDocument::where('type', $type)->whereNotNull('published_at')->latest('published_at')->firstOrFail();

    return view('legal-document', compact('document'));
})->whereIn('type', ['privacy', 'terms'])->name('legal.versioned');

// ─── Admin Panel ──────────────────────────────────────────────────────────────
Route::prefix('admin')->name('admin.')->group(function () {
    Route::get('/login', [AdminAuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [AdminAuthController::class, 'login'])->name('login.post');
    Route::post('/logout', [AdminAuthController::class, 'logout'])->name('logout');

    Route::middleware(['auth', AdminMiddleware::class])->group(function () {
        Route::get('/', AdminController::class)->name('dashboard');

        Route::get('users', [AdminUsersController::class, 'index'])->name('users.index');
        Route::get('users/{id}', [AdminUsersController::class, 'show'])->name('users.show');
        Route::patch('users/{id}', [AdminUsersController::class, 'update'])->name('users.update');

        Route::get('medicines', [AdminMedicinesController::class, 'index'])->name('medicines.index');
        Route::get('medicines/{id}', [AdminMedicinesController::class, 'show'])->name('medicines.show');

        Route::get('dose-logs', [AdminDoseLogsController::class, 'index'])->name('dose-logs.index');

        Route::get('subscriptions', [AdminSubscriptionsController::class, 'index'])->name('subscriptions.index');

        Route::get('support', [AdminSupportController::class, 'index'])->name('support.index');
        Route::get('support/{id}', [AdminSupportController::class, 'show'])->name('support.show');
        Route::post('support/{id}/reply', [AdminSupportController::class, 'reply'])->name('support.reply');
        Route::patch('support/{id}/status', [AdminSupportController::class, 'updateStatus'])->name('support.status');

        Route::resource('plans', AdminPlansController::class)->names([
            'index'   => 'plans.index',
            'create'  => 'plans.create',
            'store'   => 'plans.store',
            'edit'    => 'plans.edit',
            'update'  => 'plans.update',
            'destroy' => 'plans.destroy',
        ]);

        Route::get('audit-logs', [AdminAuditController::class, 'index'])->name('audit-logs.index');

        Route::get('notifications', [AdminNotificationsController::class, 'index'])->name('notifications.index');

        Route::get('legal', [AdminLegalController::class, 'index'])->name('legal.index');
        Route::get('legal/create', [AdminLegalController::class, 'create'])->name('legal.create');
        Route::post('legal', [AdminLegalController::class, 'store'])->name('legal.store');
        Route::get('legal/{legal}/edit', [AdminLegalController::class, 'edit'])->name('legal.edit');
        Route::put('legal/{legal}', [AdminLegalController::class, 'update'])->name('legal.update');
        Route::delete('legal/{legal}', [AdminLegalController::class, 'destroy'])->name('legal.destroy');
        Route::patch('legal/{legal}/publish', [AdminLegalController::class, 'publish'])->name('legal.publish');
    });
});
