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

// use Telegram;

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

Route::get('update', 'ApiController@updates');

//Route::get('driver', 'ApiController@respond');

//Route::get('cek', 'ApiController@me');

//Route:: get('tesHello','HelloWorld_Test@index');

//Route::get('/tesHello/{name}', 'HelloWorld_Test@show');

Route::get('capture', 'BrowsershotController@capturePage');



// Route::post('webhook', 'ApiController@webhook');

// Route::get('/set', 'ApiController@setWebhook');
// Route::post('405325770:AAG49XI9pWQSpi5OsC0hz_muUFj0QmFjndM/webhook', 'TelegramController@setWebhook');

//PERLU DIPERHATIKAN
Route::get('/set', 'webhook@setWebhook');//buat ngeset webhook
Route::get('/unset', 'webhook@unsetWebhook');//buat unset webhook
// Route::post('webhook', 'webhook@webhook');//buat akses fungsi yang pake webhook
Route::post('webhook', 'cek@webhook');
// Route::post('webhook', 'tes@webhook');
// Route::get('respond', 'tes@respond');

// Route::get('respond', 'loop@respond');//buat nge-debug
Route::get('respond', 'tes@respond');//buat nge-debug

// $updates = Telegram::getWebhookUpdates();
// Route::post('/<token>/webhook', function () {
//     $updates = Telegram::getWebhookUpdates();
//
//     return 'ok';
// });

// Route::post('/webhook', function () {
	// $updates = Telegram::getWebhookUpdates();
	// // $update = Telegram::commandsHandler(true);
	// return 'ok';
// });
