<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\VehicleController;
use App\Http\Controllers\VehicleStatusController;
use App\Http\Controllers\VehicleTransferController;
use App\Http\Controllers\EditRequestController;
use App\Http\Controllers\AttachmentController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\NotificationController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
*/

Route::get('/', function () {
    return redirect()->route('login');
});

// Authentication routes
Route::get('login', [App\Http\Controllers\Auth\LoginController::class, 'showLoginForm'])->name('login');
Route::post('login', [App\Http\Controllers\Auth\LoginController::class, 'login']);
Route::post('logout', [App\Http\Controllers\Auth\LoginController::class, 'logout'])->name('logout');


// Protected routes
Route::middleware(['auth'])->group(function () {
    // Dashboard
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    // Vehicles
    Route::resource('vehicles', VehicleController::class);

    // Vehicle Status Updates
    Route::post('/vehicles/{vehicle}/update-seizure-status', [VehicleStatusController::class, 'updateSeizureStatus'])
        ->name('vehicles.update-seizure-status');
    Route::post('/vehicles/{vehicle}/update-final-degree-status', [VehicleStatusController::class, 'updateFinalDegreeStatus'])
        ->name('vehicles.update-final-degree-status');
    Route::post('/vehicles/{vehicle}/update-valuation-status', [VehicleStatusController::class, 'updateValuationStatus'])
        ->name('vehicles.update-valuation-status');
    Route::post('/vehicles/{vehicle}/update-authentication-status', [VehicleStatusController::class, 'updateAuthenticationStatus'])
        ->name('vehicles.update-authentication-status');
    Route::post('/vehicles/{vehicle}/update-donation-status', [VehicleStatusController::class, 'updateDonationStatus'])
        ->name('vehicles.update-donation-status');
    Route::post('/vehicles/{vehicle}/update-registration-status', [VehicleStatusController::class, 'updateRegistrationStatus'])
        ->name('vehicles.update-registration-status');

    // Transfers
    Route::get('/transfers', [VehicleTransferController::class, 'index'])->name('transfers.index');
    Route::get('/vehicles/{vehicle}/transfers/create', [VehicleTransferController::class, 'create'])->name('transfers.create');
    Route::post('/vehicles/{vehicle}/transfers', [VehicleTransferController::class, 'store'])->name('transfers.store');
    Route::get('/transfers/{transfer}', [VehicleTransferController::class, 'show'])->name('transfers.show');
    Route::post('/transfers/{transfer}/complete', [VehicleTransferController::class, 'completeTransfer'])->name('transfers.complete');

    // Edit Requests
    Route::get('/edit-requests', [EditRequestController::class, 'index'])->name('edit-requests.index');
    Route::get('/vehicles/{vehicle}/edit-requests/{field}/create', [EditRequestController::class, 'create'])->name('edit-requests.create');
    Route::post('/vehicles/{vehicle}/edit-requests', [EditRequestController::class, 'store'])->name('edit-requests.store');
    Route::get('/edit-requests/{editRequest}', [EditRequestController::class, 'show'])->name('edit-requests.show');
    Route::post('/edit-requests/{editRequest}/approve', [EditRequestController::class, 'approve'])->name('edit-requests.approve');
    Route::post('/edit-requests/{editRequest}/reject', [EditRequestController::class, 'reject'])->name('edit-requests.reject');

    // Attachments
    Route::get('/attachments/{attachment}', [AttachmentController::class, 'show'])->name('attachments.show');
    Route::get('/attachments/{attachment}/download', [AttachmentController::class, 'download'])->name('attachments.download');
    Route::delete('/attachments/{attachment}', [AttachmentController::class, 'destroy'])->name('attachments.destroy');

    // Users (admin only)
    Route::resource('users', UserController::class)->middleware('role:admin');

    // Vehicle ownership transfer and external referral routes
    Route::post('/vehicles/{vehicle}/transfer-ownership', [VehicleTransferController::class, 'transferOwnership'])
    ->name('vehicles.transfer-ownership');

    Route::post('/vehicles/{vehicle}/external-referral', [VehicleTransferController::class, 'externalReferral'])
    ->name('vehicles.external-referral');

    // Notifications
    Route::get('/notifications', [NotificationController::class, 'index'])->name('notifications.index');
    Route::post('/notifications/{id}/mark-as-read', [NotificationController::class, 'markAsRead'])->name('notifications.mark-as-read');
    Route::post('/notifications/mark-all-as-read', [NotificationController::class, 'markAllAsRead'])->name('notifications.mark-all-as-read');
    Route::delete('/notifications/{id}', [NotificationController::class, 'destroy'])->name('notifications.destroy');
    Route::delete('/notifications', [NotificationController::class, 'destroyAll'])->name('notifications.destroy-all');

    Route::get('/vehicles/stalled', [VehicleController::class, 'stalled'])->name('vehicles.stalled')->middleware(['auth', 'role:admin']);

    Route::get('/api/check-recipient', function(Request $request) {
        $idNumber = $request->input('id_number');
        if (!$idNumber) {
            return response()->json(['count' => 0]);
        }

        $count = VehicleTransfer::whereNull('return_date')
            ->where('recipient_id_number', $idNumber)
            ->where('is_ownership_transfer', false)
            ->where('is_referral', false)
            ->count();

        return response()->json(['count' => $count]);
    })->middleware('auth')->name('api.check-recipient');


});
