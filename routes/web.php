<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Index\IndexController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::get('/', function () {
    return view('welcome', ['name' => 'galen']);
});

// web 路由
Route::middleware(['web'])->group(function () {
    Route::get('index/index', [IndexController::class, 'index']);
    Route::get('index/show/{id}', [IndexController::class, 'show']);
    Route::get('index/create', [IndexController::class, 'create']);
    Route::get('index/update/{id}', [IndexController::class, 'update']);
    Route::post('index/store', [IndexController::class, 'store']);
    Route::get('index/delete/{id}', [IndexController::class, 'delete']);

});
