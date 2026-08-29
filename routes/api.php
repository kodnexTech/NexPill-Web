<?php

use App\Http\Controllers\Api\V1\AppointmentController;
use App\Http\Controllers\Api\V1\AuthController;
use App\Http\Controllers\Api\V1\DashboardController;
use App\Http\Controllers\Api\V1\DoseLogController;
use App\Http\Controllers\Api\V1\FamilyController;
use App\Http\Controllers\Api\V1\MedicineController;
use App\Http\Controllers\Api\V1\NotificationController;
use App\Http\Controllers\Api\V1\PlanController;
use App\Http\Controllers\Api\V1\ProfileController;
use App\Http\Controllers\Api\V1\SideEffectController;
use App\Http\Controllers\Api\V1\SupportTicketController;
use App\Models\LegalDocument;
use Illuminate\Support\Facades\Route;

Route::prefix('v1')->group(function (): void {
    Route::get('/health', fn () => response()->json(['status' => 'ok', 'service' => 'NexPill API', 'time' => now()->toIso8601String()]));
    Route::get('/legal/{type}', fn (string $type) => LegalDocument::where('type', $type)->whereNotNull('published_at')->latest('published_at')->firstOrFail())
        ->whereIn('type', ['privacy', 'terms']);
    Route::get('/plans', [PlanController::class, 'index']);

    Route::prefix('auth')->group(function (): void {
        Route::post('/register', [AuthController::class, 'register'])->middleware('throttle:10,1');
        Route::post('/login', [AuthController::class, 'login'])->middleware('throttle:10,1');
        Route::post('/otp/request', [AuthController::class, 'requestOtp'])->middleware('throttle:5,1');
        Route::post('/otp/verify', [AuthController::class, 'verifyOtp'])->middleware('throttle:10,1');
    });

    Route::middleware(['auth:sanctum'])->group(function (): void {
        Route::post('/auth/refresh', [AuthController::class, 'refresh']);
        Route::post('/auth/logout', [AuthController::class, 'logout']);
        Route::post('/auth/logout-all', [AuthController::class, 'logoutAll']);

        Route::get('/dashboard', DashboardController::class);
        Route::get('/adherence', [DashboardController::class, 'adherence']);
        Route::apiResource('medicines', MedicineController::class);
        Route::post('/medicines/{medicine}/prescription', [MedicineController::class, 'uploadPrescription']);
        Route::get('/medicines/{medicine}/prescription', [MedicineController::class, 'downloadPrescription']);
        Route::post('/medicines/{medicine}/refills', [MedicineController::class, 'refill']);
        Route::get('/doses', [DoseLogController::class, 'index']);
        Route::post('/doses', [DoseLogController::class, 'store']);
        Route::post('/doses/{doseLog}/actions', [DoseLogController::class, 'action']);

        Route::get('/family', [FamilyController::class, 'index']);
        Route::post('/family/invitations', [FamilyController::class, 'invite']);
        Route::post('/family/invitations/accept', [FamilyController::class, 'accept']);
        Route::post('/family/dependents', [FamilyController::class, 'addDependent']);
        Route::post('/family/{connection}/nudge', [FamilyController::class, 'nudge']);
        Route::get('/family/{connection}/medicines', [FamilyController::class, 'medicines']);
        Route::delete('/family/{connection}', [FamilyController::class, 'destroy']);

        Route::apiResource('appointments', AppointmentController::class);
        Route::get('/notifications', [NotificationController::class, 'index']);
        Route::get('/notifications/unread-count', [NotificationController::class, 'unreadCount']);
        Route::put('/notifications/read-all', [NotificationController::class, 'readAll']);
        Route::put('/notifications/{notification}/read', [NotificationController::class, 'read']);

        Route::get('/profile', [ProfileController::class, 'show']);
        Route::put('/profile', [ProfileController::class, 'update']);
        Route::put('/profile/notification-preferences', [ProfileController::class, 'updatePreferences']);
        Route::post('/profile/devices', [ProfileController::class, 'registerDevice']);
        Route::delete('/profile/devices/{deviceId}', [ProfileController::class, 'removeDevice']);
        Route::get('/profile/export', [ProfileController::class, 'export'])->middleware('throttle:2,60');
        Route::delete('/profile', [ProfileController::class, 'destroy'])->middleware('throttle:2,60');

        Route::get('/side-effects', [SideEffectController::class, 'index']);
        Route::post('/side-effects', [SideEffectController::class, 'store']);
        Route::delete('/side-effects/{sideEffect}', [SideEffectController::class, 'destroy']);
        Route::get('/support/tickets', [SupportTicketController::class, 'index']);
        Route::post('/support/tickets', [SupportTicketController::class, 'store']);
        Route::post('/support/tickets/{ticket}/replies', [SupportTicketController::class, 'reply']);
        Route::get('/subscription', [PlanController::class, 'current']);
    });
});

Route::fallback(fn () => response()->json(['success' => false, 'message' => 'API route not found.'], 404));
