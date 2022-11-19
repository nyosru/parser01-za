<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

use App\Http\Controllers\GoodController;
use App\Http\Controllers\ParserController;

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



// 500 раз в минуту
Route::get('parsing', [ ParserController::class, 'go']);




Route::prefix('good')->group(function () {
    // парсим полные страницы товаров
    Route::get('loadingPages/{kolvo?}', [GoodController::class, 'loadingPages'])
        ->where('kolvo', '[0-9]+');
    // Route::get('loadingPagesPhantom', [GoodController::class, 'loadingPagesPhantom']);
    // Route::get('parsingGoods', [GoodController::class, 'parsingGoods']);
});
