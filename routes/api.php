<?php

use App\Http\Controllers\V1\AuthController;
use App\Http\Controllers\V1\TransactionController;
use App\Http\Controllers\V1\UserWalletController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/
Route::post('register', [AuthController::class, 'register']);
Route::post('login', [AuthController::class, 'login']);

Route::group(['middleware' => 'auth:api'], function(){

    Route::prefix('/user')->group(function(){
        Route::get('/profile', [AuthController::class, 'getUserProfile']);
        Route::get('/all', [AuthController::class, 'showAll']);
        Route::post('/logout', [AuthController::class, 'logout']);
        Route::prefix('/wallet')->group(function(){
            Route::post('/top-up', [UserWalletController::class, 'topUpBalance']);
            Route::get('/saldo', [UserWalletController::class, 'getSaldoBalance']);
            Route::put('/with-draw', [UserWalletController::class, 'withDrawBalance']);
            Route::post('/transfer/{walletId}', [UserWalletController::class, 'transferBalance']);
            Route::get('/history', [TransactionController::class, 'getReport']);
            
        });
    });

});
