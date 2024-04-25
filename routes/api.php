<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\OtpController;
use App\Http\Controllers\Api\ApiController;
use App\Http\Controllers\Api\FilterController;
use App\Http\Controllers\Api\ResetPasswordController;
use App\Http\Controllers\Api\ForgotPasswordController;
use App\Http\Controllers\Api\ItemController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

//Register and Login section
Route::post("register", [ApiController::class, "register"])->name('register');
Route::post("login", [ApiController::class, "login"])->name('login');


//forgot password implementation (open route)
Route::get('password/forgot', [ForgotPasswordController::class, 'showForgotPasswordForm'])->name('forget.password.get');
Route::post('password/forgot', [ForgotPasswordController::class, 'forgotPasswordPost'])->name('forgot.password.post');
Route::get('password/reset/{token}', [ResetPasswordController::class, 'showResetPasswordForm'])->name('reset.passport.get');
Route::post('password/reset', [ResetPasswordController::class, 'resetPasswordPost'])->name('reset.password.post');

/**
 * Important this Otp section has not been tested (no twillio credentials)
 */
// Otp section
Route::get('otp/login', [OtpController::class, 'otpLogin'])->name('otp.login');
Route::post('otp/generate', [OtpController::class, 'otpGenerate'])->name('otp.generate');
Route::get('otp/verification/{user_id}', [OtpController::class, 'otpVerification'])->name('otp.verification');
Route::post('otp/login', [OtpController::class, 'loginWithOtp'])->name('otp.getlogin');

Route::get('scan/{code}', [ItemController::class, 'scan'])->name('scan');
//protected route
Route::group([
    "middleware" => ["auth:api"]
], function() {
    Route::get('inventories', [ApiController::class, 'inventories'])->name('all.inventories');
    Route::get('inventories/items', [ApiController::class, 'items'])->name('items.index');
    Route::post('inventories/items', [ApiController::class, 'addItem'])->name('item.add');
    

    Route::get('inventories/items/{item}', [ApiController::class, 'item'])->name('item.show');
    Route::put('inventories/items/{item}', [ApiController::class, 'updateItem'])->name('item.update');
    
    Route::get('inventories/favorite/items', [ApiController::class, 'favorite'])->name('items.favorite');
    Route::get('inventories/archived/items', [ApiController::class, 'archivedItem'])->name('items.archived');
    Route::get('inventories/estimatedItems', [ApiController::class, 'estimatedItem'])->name('items.estimated');
    
    // try converting the method below to a get method
    Route::post("inventories/items/search", [FilterController::class, "search"])->name('items.search');
    

    Route::get('categories', [ApiController::class, 'categories'])->name('category.index');
    Route::post('category', [ApiController::class, 'addCategory'])->name('category.add');
    Route::post("categories/filterByCategoryName", [FilterController::class, "filterController"])->name('categoryfilter.name');
    Route::post("categories/filterByOrder", [FilterController::class, "orderController"])->name('categoryfilter.order');
    
    
    // table for contact support
    Route::get('/contact-support', [ApiController::class, 'contactSupport'])->name('contact.support');
    
    //table for privacy support
    Route::get('/privacy-policy', [ApiController::class, 'privacyPolicy'])->name('privacy.policy');
    // Route::get('', [ApiController::class,''])->name('');
    Route::get('/term-conditions', [ApiController::class, 'termsConditions'])->name('terms.condition');
    // Route::get("items/search/{searchParam}", [FilterController::class, "search"])->name('item.search');
    
    Route::get("profile", [ApiController::class, "profile"])->name('profile.get');
    Route::post("profile/edit", [ApiController::class, "profileEdit"])->name('profile.edit');
    Route::get("logout", [ApiController::class, "logout"])->name('logout');
    Route::delete('delete', [ApiController::class, "deleteAccount"])->name('deleteAccount');
});