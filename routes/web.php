<?php

use App\Http\Controllers\admin\CategoriesController;
use App\Http\Controllers\admin\CodesController;
use App\Http\Controllers\admin\ProductController;
use App\Http\Controllers\admin\SliderController;
use App\Http\Controllers\admin\UsersController;
use App\Http\Controllers\admin\VariantController;
use App\Http\Controllers\CronJobController;
use App\Http\Controllers\WebHooksController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\admin\DashboardController;
use App\Http\Controllers\admin\OrdersController;
use App\Http\Controllers\ProfileController;

Route::get('/', function () {
    return view('test');
});

// Authenticated Admin Routes
Route::middleware('auth')->prefix('admin')->as('admin.')->group(function () {

    // Dashboard
    Route::get('dashboard', [DashboardController::class, 'index'])->name('dashboard');

    Route::resource('products', ProductController::class);

    // Profile Page
    Route::get('profile', [ProfileController::class, 'show'])->name('profile');

    // Orders
    Route::get('orders', [OrdersController::class, 'index'])->name('orders');
    Route::post('orders/status-update', [OrdersController::class, 'updateStatus'])->name('orders.updateStatus');
    Route::get('orders/{id}/edit', [OrdersController::class, 'edit'])->name('orders.edit');
    Route::delete('orders/{id}', [OrdersController::class, 'destroy'])->name('orders.destroy');


    Route::resource('variant', VariantController::class);
    Route::resource('categories', CategoriesController::class);
    Route::get('variants/{id}', [VariantController::class, 'variant'])->name('variant');


    Route::resource('sliders', SliderController::class);
    Route::resource('users', UsersController::class);


    //codes
    Route::resource('codes', CodesController::class);
    Route::get('codes/{id}', [CodesController::class, 'code'])->name('code');

});

// Fallback Route for 404
Route::fallback(function () {
    return redirect()->route('admin.dashboard')->with('error', 'Page not found.');
});

Route::get('auto-top-up-cron',[CronJobController::class,'freeFireAutoTopUpJob']);

// Authentication Routes
require __DIR__ . '/auth.php';
