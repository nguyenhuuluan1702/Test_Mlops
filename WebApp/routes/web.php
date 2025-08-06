<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\Admin\AdminController;
use App\Http\Controllers\User\UserController;

// Include test routes for debugging
if (app()->environment('local')) {
    include __DIR__ . '/test.php';
}

// Redirect root to login
Route::get('/', function () {
    return redirect()->route('login');
});

// Search route for error pages
Route::get('/search', function () {
    return redirect()->route('login')->with('message', 'Please login to use search functionality.');
})->name('search');

// Authentication routes
Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
Route::post('/login', [AuthController::class, 'login']);
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');
Route::get('/dashboard', [AuthController::class, 'dashboard'])->name('dashboard');

// Admin routes
Route::prefix('admin')->name('admin.')->middleware(['auth', 'admin'])->group(function () {
    Route::get('/dashboard', [AdminController::class, 'dashboard'])->name('dashboard');
    
    // User management
    Route::get('/users', [AdminController::class, 'users'])->name('users');
    Route::get('/users/create', [AdminController::class, 'createUser'])->name('users.create');
    Route::post('/users', [AdminController::class, 'storeUser'])->name('users.store');
    Route::get('/users/{user}/edit', [AdminController::class, 'editUser'])->name('users.edit');
    Route::put('/users/{user}', [AdminController::class, 'updateUser'])->name('users.update');
    Route::post('/users/{user}/reset-password', [AdminController::class, 'resetPassword'])->name('users.reset-password');
    Route::delete('/users/{user}', [AdminController::class, 'deleteUser'])->name('users.delete');
    Route::delete('/users/{user}/force', [AdminController::class, 'forceDeleteUser'])->name('users.force-delete');
    Route::post('/users/{user}/anonymize', [AdminController::class, 'anonymizeUser'])->name('users.anonymize');
    
    // Model management
    Route::get('/models', [AdminController::class, 'models'])->name('models');
    Route::get('/models/create', [AdminController::class, 'createModel'])->name('models.create');
    Route::post('/models', [AdminController::class, 'storeModel'])->name('models.store');
    Route::get('/models/{model}/edit', [AdminController::class, 'editModel'])->name('models.edit');
    Route::put('/models/{model}', [AdminController::class, 'updateModel'])->name('models.update');
    Route::delete('/models/{model}', [AdminController::class, 'deleteModel'])->name('models.delete');
    Route::delete('/models/{model}/force', [AdminController::class, 'forceDeleteModel'])->name('models.force-delete');
    Route::post('/models/{model}/test', [AdminController::class, 'testModel'])->name('models.test');
    
    // Prediction features for admin
    Route::get('/predict', [AdminController::class, 'predict'])->name('predict');
    Route::post('/predict', [AdminController::class, 'makePrediction'])->name('predict.make');
    Route::get('/history', [AdminController::class, 'history'])->name('history');
});

// User routes
Route::prefix('user')->name('user.')->middleware(['auth', 'user'])->group(function () {
    Route::get('/dashboard', [UserController::class, 'dashboard'])->name('dashboard');
    Route::get('/predict', [UserController::class, 'predict'])->name('predict');
    Route::post('/predict', [UserController::class, 'makePrediction'])->name('predict.make');
    Route::get('/history', [UserController::class, 'history'])->name('history');
    Route::get('/profile', [UserController::class, 'profile'])->name('profile');
    Route::put('/profile', [UserController::class, 'updateProfile'])->name('profile.update');
    Route::get('/security', [UserController::class, 'security'])->name('security');
    Route::post('/security/change-password', [UserController::class, 'changePassword'])->name('security.change-password');
});
