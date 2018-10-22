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

Route::prefix('user')->group(function () {
    Route::post('signup', 'UserController@signup');
    Route::post('login', 'UserController@login');
    Route::middleware('auth:api')->group(function () {
        Route::get('details', 'UserController@getDetails');
        Route::post('edit', 'UserController@editDetails');
        Route::post('logout', 'UserController@logout');
        Route::get('logout-all', 'UserController@logoutAll');
        Route::get('sessions', 'UserController@getAllSessions');
    });
});
Route::post('articleStore','Articles@articleStore');
Route::get('articles',function(Request $request){
    $data=DB::table('admin_articles')->get();
    return response()->json($data);
});
Route::post('updateArticle/{id}','Articles@updateArticle');
Route::post('update/{id}','Articles@update');
Route::get('deleteArticles/{id}', 'Articles@articleDelete');
Route::get('articleDetails/{id}','Articles@getArticle');


