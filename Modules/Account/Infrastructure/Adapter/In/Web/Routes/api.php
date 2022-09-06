<?php

use Illuminate\Support\Facades\Route;
use Modules\Account\Infrastructure\Adapter\In\Web\Http\Controllers\SendMoneyController;

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

Route::post('/accounts/send/{sourceAccountId}/{targetAccountId}/{amount}', [
    SendMoneyController::class, 'sendMoney',
]);
