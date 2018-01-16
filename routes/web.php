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

//use Telegram;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/testing', function () {
    return view('testing');
});

Route::get('/sendMsg', function () {
    return view('sendMsg');
});

Route::get('/me', 'ApiController@me');

Route::get('driver', 'ApiController@respond');

Route::get('cek', 'ApiController@me');

Route:: get('tesHello','HelloWorld_Test@index');

Route::get('/tesHello/{name}', 'HelloWorld_Test@show');

Route::get('capture', 'BrowsershotController@capturePage');

Route::get('/setwebhook', 'ApiController@setWebhook');

Route::get('/unsetwebhook', 'ApiController@unsetWebhook');

// Route::post('webhook', 'ApiController@webhook');

Route::post('webhook', 'TelegramController@webhook');

Route::get('respond', 'TelegramController@respond');

// Route::post('/webhook', function () {
	// $updates = Telegram::getWebhookUpdates();
	// // $update = Telegram::commandsHandler(true);
	// return 'ok';
// });
