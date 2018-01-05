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

Route::get('/', function () {
    return view('welcome');
});

Route::get('telegram/getme','TelegramController@getMe');

Route::get('telegram/getupdates','TelegramController@getupdates');

// Route::get('telegram/respon','TelegramController@respon');

Route::get('telegram/removewebhook','TelegramController@webhook');

Route::get('setwebhook', 'TelegramController@setWebHook');

Route::post('502539981:AAE7FDMraFwOV40U8NNR4MLpIkmnE1J7r84/webhook', 'TelegramController@webhook');
