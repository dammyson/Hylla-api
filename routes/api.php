<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\OtpController;
use App\Http\Controllers\Api\ApiController;
use App\Http\Controllers\Api\FilterController;
use App\Http\Controllers\Api\ResetPasswordController;
use App\Http\Controllers\Api\ForgotPasswordController;
use App\Http\Controllers\Api\ItemController;
use App\Http\Controllers\Api\CategoryController;
use App\Http\Controllers\Api\NotificationController;
use App\Http\Controllers\Api\ProfileController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\Api\RecallController as ApiRecallController;

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



Route::group(['prefix' => 'auth'], function($router) {
    $router->get('google/redirect', [ApiController::class, 'googleRedirect']);
    $router->get('google/callback', [ApiController::class, 'gooogleCallback']);
    
});

//forgot password implementation (open route)
Route::get('password/forgot', [ForgotPasswordController::class, 'showForgotPasswordForm'])->name('forget.password.get');
Route::post('password/forgot', [ForgotPasswordController::class, 'forgotPasswordPost'])->name('forgot.password.post');
Route::get('password/reset/{token}', [ResetPasswordController::class, 'showResetPasswordForm'])->name('reset.passport.get');
Route::post('password/reset', [ResetPasswordController::class, 'resetPasswordPost'])->name('reset.password.post');

//
Route::post('forgetpassword',  [OtpController::class,'generatePinForgetPassword']);
Route::post('verifycode',  [OtpController::class,'VerifyOTP']);
Route::post('resetPassword', [ResetPasswordController::class, 'resetPassword']);

/**
 * Important this Otp section has not been tested (no twillio credentials)
 */
// Otp section
Route::get('otp/login', [OtpController::class, 'otpLogin'])->name('otp.login');
Route::post('otp/generate', [OtpController::class, 'otpGenerate'])->name('otp.generate');
Route::get('otp/verification/{user_id}', [OtpController::class, 'otpVerification'])->name('otp.verification');
Route::post('otp/login', [OtpController::class, 'loginWithOtp'])->name('otp.getlogin');


//protected route
Route::group([
    "middleware" => ["auth:api"]
], function() {
    Route::post("support", [OtpController::class, "SendMail"])->name('support');
    Route::get('inventories', [ItemController::class, 'inventories'])->name('all.inventories');
    Route::get('recalls', [ItemController::class, 'getRecall'])->name('all.recalls');
    Route::get('inventories/items', [ItemController::class, 'items'])->name('items.index');
    Route::post('inventories/items', [ItemController::class, 'addItem'])->name('item.add');
    
    Route::get('inventories/items/{item}', [ItemController::class, 'item'])->name('item.show');
    Route::patch('inventories/items/{item}', [ItemController::class, 'updateItem'])->name('item.update');
    
    Route::get('inventories/favorite/items', [ItemController::class, 'favorite'])->name('items.favorite');
    Route::get('inventories/archived/items', [ItemController::class, 'archivedItem'])->name('items.archived');
    Route::get('inventories/estimatedItems', [ItemController::class, 'estimatedItem'])->name('items.estimated');
    
    // try converting the method below to a get method
    Route::post("inventories/items/search", [FilterController::class, "search"])->name('items.search');
    
    Route::get('categories', [CategoryController::class, 'categories'])->name('category.index');
    Route::get('categories/{id}', [CategoryController::class, 'item'])->name('category.index');
    Route::patch('categories/update/{id}', [CategoryController::class, 'updateCategory'])->name('category.update');
    Route::patch('categories/{categoryId}/products/{productId}/add', [CategoryController::class, 'addProductToCategory'])->name('category.product.add');
    Route::patch('categories/{categoryId}/products/{productId}/remove', [CategoryController::class, 'removeProductFromCategory'])->name('category.product.remove');
    Route::post('category', [CategoryController::class, 'addCategory'])->name('category.add');
    Route::post("categories/filterByCategoryName", [FilterController::class, "filterController"])->name('categoryfilter.name');
    Route::post("categories/filterByOrder", [FilterController::class, "orderController"])->name('categoryfilter.order');
    
    
    // table for contact support
    Route::get('/contact-support', [ApiController::class, 'contactSupport'])->name('contact.support');
    
    //table for privacy support
    Route::get('/privacy-policy', [ApiController::class, 'privacyPolicy'])->name('privacy.policy');
    // Route::get('', [ApiController::class,''])->name('');
    Route::get('/term-conditions', [ApiController::class, 'termsConditions'])->name('terms.condition');
    // Route::get("items/search/{searchParam}", [FilterController::class, "search"])->name('item.search');
    
    Route::get("profile", [ProfileController::class, "profile"])->name('profile.get');
    Route::post("profile/edit", [ProfileController::class, "profileEdit"])->name('profile.edit');
    Route::get("logout", [ApiController::class, "logout"])->name('logout');
    Route::delete('delete', [ApiController::class, "deleteAccount"])->name('deleteAccount');
    Route::post('/profile/changePassword',  [ProfileController::class,'changePassword']); 
    
    Route::get('scan/{code}', [ProductController::class, 'scan'])->name('scan');
    Route::get('loadimages', [ProductController::class, 'loadImages'])->name('loadimages');


    Route::get('/notifications', [NotificationController::class, 'test'])->name('');

    
    Route::get('/user/notifications', [NotificationController::class, 'listUserNotifications'])->middleware('auth:api');
    Route::get('/user/notifications/markAsRead/{id}', [NotificationController::class, 'markAsRead'])->middleware('auth:api');
    
    Route::get('/user/notifications/list-push-notification', [NotificationController::class, 'listPushNotification'])->middleware('auth:api');
    Route::get('/user/notifications/markNotificationAsRead/{id}', [NotificationController::class, 'markNotificationAsRead'])->middleware('auth:api');

    Route::get('/recalls', [ApiRecallController::class, 'recallItems'])->name('');

});