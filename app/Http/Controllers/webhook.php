<?php
namespace App\Http\Controllers;

//use Telegram;
use Telegram\Bot\Api;
use Telegram\Bot\Keyboard\Keyboard;
use Illuminate\Support\Facades\DB;
use Telegram\Bot\Laravel\Facades\Telegram;
use App\Http\Requests;
use App\Http\Controllers\Controller;
use Exception;
use DateTime;
use DateInterval;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class webhook extends Controller
{//awal kelas
	public function setWebhook()
  	{
		$response = Telegram::setWebhook(['url' => 'https://e8b0217e.ngrok.io/tes/public/webhook',]);
		dd($response);
	}//akhir fungsi

	public function unsetWebhook()
	{
		$response = Telegram::removeWebhook();
		dd($response);
	}//akhir fungsi

}//akhir kelas

?>
