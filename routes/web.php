<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\CatController;
use App\Http\Controllers\GoodController;
use App\Http\Controllers\ParserController;

Route::get('/status', [ParserController::class, 'index']);
Route::get('/nowStep', [ParserController::class, 'howNextStep']);

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
    // перенесено в апи
    // Route::get('loadingPages/{kolvo?}', [GoodController::class, 'loadingPages']);
    Route::get('loadingPagesPhantom', [GoodController::class, 'loadingPagesPhantom']);
    Route::get('parsingGoods', [GoodController::class, 'parsingGoods']);
});

Route::get('/1', function () {

    $in = [];
    $in['items'] = [];

    $in['items'][] = ['href' => '/status', 'name' => 'общий обзор'];

    $in['items'][] = ['href' => '/ss', 'name' => 'пустая страничка'];
    $in['items'][] = ['href' => '/cat/list', 'name' => 'список каталогов'];
    $in['items'][] = ['href' => '/cat/get', 'name' => 'cat парсим со страницы'];

    $in['items'][] = ['href' => '/cat/get1page', 'name' => 'парсим первую страницу каждого каталога (смотрим сколько страниц и вложенность в каталоги)'];
    $in['items'][] = ['href' => '/cat/creatListScanPage', 'name' => 'сформировать список страниц с товарами для скана'];

    $in['items'][] = ['href' => '/cat/loadingPages', 'name' => 'загрузка страниц каталогов'];

    $in['items'][] = ['href' => '/api/good/loadingPages', 'name' => 'API / good / загрузка страниц товаров (стар)'];
    $in['items'][] = ['href' => '/good/loadingPagesPhantom', 'name' => 'загрузка страниц товаров (норм)'];

    $in['items'][] = ['href' => '/good/parsingGoods', 'name' => 'парсим товар по полной странице ( new > full )'];

    // $in['items'][] = ['href' => '/good/scan', 'name' => 'парсим товар по полной странице ( new > full )'];

    // $in['items'][] = ['href' => 1, 'name' => 22];
    // $in['items'][] = ['href' => 1, 'name' => 22];
    // $in['items'][] = ['href' => 1, 'name' => 22];

    return view('welcome1', $in);
});

Auth::routes();

Route::get('/home', [App\Http\Controllers\HomeController::class, 'index'])->name('home');

Route::get('{any}', function () {
    return view('layouts.app');
})->where('any', '.*');
