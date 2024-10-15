<?php

use App\Http\Controllers\UrlController;
use Illuminate\Support\Facades\Route;

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

Route::get('/', function () {
    return '©' .  Carbon\Carbon::now()->format('Y') . ' ' . env('APP_NAME');
});

Route::apiResource('url', UrlController::class)->only(['index', 'store', 'show', 'destroy'])
    ->parameters(['url', 'url']);

Route::get('url/{url_key}', [UrlController::class, 'show']);
