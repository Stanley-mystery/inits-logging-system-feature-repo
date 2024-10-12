<?php

use App\Http\Controllers\Web\Admin\AdminController;
use App\Http\Controllers\Web\Oauth\OauthController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Web\Auth\AuthenticationsController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/
Route::get("/", function(){
    return view('welcome');
});

Route::group(['prefix' => 'v1'], function () {
    Route::get('/login', [AuthenticationsController::class, 'login'])->name('login-form');
    Route::post('/login', [AuthenticationsController::class, 'authenticateUser'])->name('login');
    Route::get('/google/auth/callback', [OauthController::class, 'handleCallback']);
});

Route::middleware(['auth', 'admin'])->prefix('v1')->group(function () {
    Route::get("/dashboard", [AdminController::class, 'index'])->name('dashboard');
});

 
Route::get('/auth/redirect',[OauthController::class, 'redirectToGoogleAuth'] );
 
Route::post("/logout", [AuthenticationsController::class, 'logout']);
