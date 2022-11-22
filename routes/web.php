<?php

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
// Home
use App\Http\Controllers\Auth\RegisterController;

Route::get('/', 'OverviewController@index');

/*
// Cards
Route::get('cards', 'CardController@list');
Route::get('cards/{id}', 'CardController@show');

// API
Route::put('api/cards', 'CardController@create');
Route::delete('api/cards/{card_id}', 'CardController@delete');
Route::put('api/cards/{card_id}/', 'ItemController@create');
Route::post('api/item/{id}', 'ItemController@update');
Route::delete('api/item/{id}', 'ItemController@delete');
*/

// Authentication
Route::get('login', 'Auth\LoginController@showLoginForm')->name('login');
Route::post('login', 'Auth\LoginController@login');

Route::get('logout', 'Auth\LoginController@logout')->name('logout');

Route::get('register', [RegisterController::class, 'showRegistrationForm'])->name('register');
Route::post('register', [RegisterController::class, 'register']);

// Games
Route::get('bestsellers', 'GameController@showBestSellers')->name('bestsellers');
Route::get('comingsoon', 'GameController@showComingSoon')->name('comingsoon');
Route::get('all', 'GameController@showAll')->name('viewallgames');

//Product Information
Route::get('details/{game_id}', 'GameController@index')->name('game');

//Categories
Route::get('categories', 'CategoryController@index')->name('categories');

