<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\CatController;
use App\Http\Controllers\GoodController;

Route::get('/ss', function () {
    dd(123);
});

Route::get('/cat/get', [CatController::class, 'get']);
Route::get('/cat/get1page', [CatController::class, 'get1page']);
Route::get('/cat/list', [CatController::class, 'showList']);
Route::get('/cat/creatListScanPage', [CatController::class, 'creatListScanPage']);
// загрузка незагруженных страниц каталогов 
Route::get('/cat/loadingPages', [CatController::class, 'loadingPages']);

Route::prefix('good')->group(function () {
    Route::get('loadingPages', [GoodController::class, 'loadingPages']);
    Route::get('loadingPagesPhantom', [GoodController::class, 'loadingPagesPhantom']);
    Route::get('parsingGoods', [GoodController::class, 'parsingGoods']);
});

Route::get('/', function () {

    $in = [];
    $in['items'] = [];

    $in['items'][] = ['href' => '/ss', 'name' => 'пустая страничка'];
    $in['items'][] = ['href' => '/cat/list', 'name' => 'список каталогов'];
    $in['items'][] = ['href' => '/cat/get', 'name' => 'cat парсим со страницы'];

    $in['items'][] = ['href' => '/cat/get1page', 'name' => 'парсим первую страницу каждого каталога (смотрим сколько страниц и вложенность в каталоги)'];
    $in['items'][] = ['href' => '/cat/creatListScanPage', 'name' => 'сформировать список страниц с товарами для скана'];

    $in['items'][] = ['href' => '/cat/loadingPages', 'name' => 'загрузка страниц каталогов'];
    $in['items'][] = ['href' => '/good/loadingPages', 'name' => 'загрузка страниц товаров (стар)'];
    $in['items'][] = ['href' => '/good/loadingPagesPhantom', 'name' => 'загрузка страниц товаров (норм)'];

    $in['items'][] = ['href' => '/good/parsingGoods', 'name' => 'парсим товар по полной странице ( new > full )'];

    // $in['items'][] = ['href' => '/good/scan', 'name' => 'парсим товар по полной странице ( new > full )'];

    // $in['items'][] = ['href' => 1, 'name' => 22];
    // $in['items'][] = ['href' => 1, 'name' => 22];
    // $in['items'][] = ['href' => 1, 'name' => 22];

    return view('welcome1', $in);
});
