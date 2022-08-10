<?php

use Illuminate\Http\Request;
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

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

Route::get('/drops', 'App\Http\Controllers\DropsController@index');
Route::post('/drops/store', 'App\Http\Controllers\DropsController@store');
Route::post('/drops/vote', 'App\Http\Controllers\DropsController@vote');
Route::post('/drops/reaction/vote', 'App\Http\Controllers\DropsController@reactionVote');
Route::get('/drops/rankedByDates', 'App\Http\Controllers\DropsController@rankedByDates');
Route::get('/drops/rankedByVotes', 'App\Http\Controllers\DropsController@rankedByVotes');
Route::get('/drops/newlyDrops', 'App\Http\Controllers\DropsController@newlyDrops');
Route::get('/drops/completedDrops', 'App\Http\Controllers\DropsController@completedDrops');

Route::get('/drops/show/{name}', 'App\Http\Controllers\DropsController@show');
Route::get('/drops/featured', 'App\Http\Controllers\DropsController@getFeatured');

Route::get('/sliders', 'App\Http\Controllers\SlidersController@index');
// Banner Ad
Route::get('/banner', 'App\Http\Controllers\BannerController@index');

Route::post('/newsletters/subscribe', 'App\Http\Controllers\NewslettersController@create');

Route::get('/sections/{key}', 'App\Http\Controllers\SectionsController@index');

Route::post('/projects/store', 'App\Http\Controllers\ProjectsController@store');

Route::get('/advertise', 'App\Http\Controllers\AdvertiseController@index');

// Collections

Route::get('/collections/{collection_name}', 'App\Http\Controllers\CollectionsController@index');
Route::get('/collections', 'App\Http\Controllers\CollectionsController@getAllCollections');
Route::get('/collection/{slug}', 'App\Http\Controllers\CollectionsController@getCollection');
Route::get('/assets/{slug}', 'App\Http\Controllers\CollectionsController@assets');
Route::get('/assets/{slug}/{id}', 'App\Http\Controllers\CollectionsController@getAsset');
Route::post('/remove/assets', 'App\Http\Controllers\CollectionsController@removeAssets');
Route::post('/store/assets', 'App\Http\Controllers\CollectionsController@storeAssets');

Route::get('/project/statuses', 'App\Http\Controllers\ProjectPageController@index');
