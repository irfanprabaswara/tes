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
		$response = Telegram::setWebhook(['url' => 'https://90f4d141.ngrok.io/tes/public/webhook',]);
		dd($response);
	}

	public function unsetWebhook()
	{
		$response = Telegram::removeWebhook();
		dd($response);
	}

	function webhook()
	{//awal func webhook
		$request = Telegram::getWebhookUpdates();//buat ngeget chat
		$chatid = $request->getMessage()->getChat()->getId();//buat ngeget id pengirim
		$text = $request->getMessage()->getText();//buat ngeget text
		$username = $request->getMessage()->getChat()->getUsername();//buat ngeget username

		switch($text) {
				case $text === '/start':
					$this->showWelcomeMessage($chatid);
					break;
				case $text === '/driver':
					$this->showDriverList($chatid);
					break;


				default:
				   // $info = 'I do not understand what you just said. Please choose an option';
				   // $this->showMenu($chatid, $info);
          $this->defaultMessage($chatid);
				  break;
		}

	}//akhir function webhook

	public function showWelcomeMessage($chatid)//ini untuk menampilkan pesan selamat datang
  	{
		$message = "Semangat PKL guys ^^";
		$response = Telegram::sendMessage([
			'chat_id' => $chatid,
			'parse_mode' => 'markdown',
			'text' => $message
		]);
		$response = Telegram::sendMessage([
			'chat_id' => 437329516,
			'parse_mode' => 'markdown',
			'text' => "akun : ".$chatid." telah mengirim command /start ke bot anda"
		]);
	}//ini akhir fungsi

  public function defaultMessage($chatid) //ini untuk menampilkan pesan default
  {
		$message = "Ini Default Message terbaru";
		$response = Telegram::sendMessage([
			'chat_id' => $chatid,
			'parse_mode' => 'markdown',
			'text' => $message
		]);
		$response = Telegram::sendMessage([
			'chat_id' => 437329516,
			'parse_mode' => 'markdown',
			'text' => "akun : ".$chatid." telah mengirim pesan ke bot anda"
		]);
	}//ini akhir fungsi

	public function showDriverList($chatid){//untuk menampilkan Driver beserta statusnya
		$message="";
		$result = DB::table('driver')->get();
		$message = "*DAFTAR DRIVER KANWIL YOGYAKARTA* \n\n";
		if ($result->count()>0){
			for ($i=0;$i<$result->count();$i++){
				$message .= "*".$result[$i]->nama."*\n";
				if($result[$i]->status ==""){
					$message .= "Status : Kosong\n";
				}else{
					$message .= "Status : ".$result[$i]->status."\n";
				}
				$message .= "\n";
			}
		}

		$response = Telegram::sendMessage([
			'chat_id' => $chatid,
			'parse_mode' => 'markdown',
			'text' => $message
		]);
	}//ini akhir fungsi
}//akhir kelas





?>