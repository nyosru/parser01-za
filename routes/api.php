<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

use App\Http\Controllers\GoodController;

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

// Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
//     return $request->user();
// });

Route::prefix('good')->group(function () {
    // парсим полные страницы товаров
    Route::get('loadingPages/{kolvo?}', [GoodController::class, 'loadingPages']);
    // Route::get('loadingPagesPhantom', [GoodController::class, 'loadingPagesPhantom']);
    // Route::get('parsingGoods', [GoodController::class, 'parsingGoods']);
});
