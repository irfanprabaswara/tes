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
		$response = Telegram::setWebhook(['url' => 'https://c4d54cdd.ngrok.io/tes/public/webhook',]);
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
		// $text = $request['message']['text'];
		$username = $request->getMessage()->getChat()->getUsername();//buat ngeget username
		// $username = $request['message']['chat']['username'];
		$keyboard = [//ini buat bikin keyboard angka
				['/driver'],['/start'],['/website'],['/contact'],['/tiket'],['/hideKeyboard']
		];//ini akhir dari keyboard

		switch($text) {
				case $text === '/start':
					$this->showWelcomeMessage($chatid);
					break;
				case $text === '/driver':
					$this->showDriverList($chatid);
					break;
				case $text === '/menu':
					$this->showKeyboard($chatid, $keyboard);
					break;
				case $text==='/hideKeyboard':
					$this->hideKeyboard($chatid);
					break;
				case $text==='/website':
					$this->showWebsite($chatid);
					break;
				case $text==='/contact':
					$this->showContact($chatid);
					break;
				case $text==='/tiket':
					$this->showTiket($chatid);
					break;
				// case $text==='/button':
				// 	$this->showMenuButton($chatid);
				// 	break;
				// case $text==='/menu':
				// 	$this->showMenu($chatid);
				// 	break;


				default:
				   // $info = 'I do not understand what you just said. Please choose an option';
				   // $this->showMenu($chatid, $info);
          $this->defaultMessage($chatid, $text, $username);
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
		// $response = Telegram::sendMessage([
		// 	'chat_id' => 437329516,
		// 	'parse_mode' => 'markdown',
		// 	'text' => "akun : ".$chatid." telah mengirim command /start ke bot anda"
		// ]);
	}//ini akhir fungsi

  public function defaultMessage($chatid, $text, $username) //ini untuk menampilkan pesan default
  {
		$message = "Ini Default Message terbaru";
		$response = Telegram::sendMessage([
			'chat_id' => $chatid,
			// 'parse_mode' => 'markdown',
			'text' => $message
		]);
		$response = Telegram::sendMessage([
			'chat_id' => 437329516,
			// 'parse_mode' => 'markdown',
			'text' => "akun : ".$username." telah mengirim pesan ".$text." ke bot anda"
		]);
	}//ini akhir fungsi

	public function showKeyboard($chatid, $keyboard)//ini buat bikin fungsi tampil Keyboard
	{
		$reply_markup = Telegram::replyKeyboardMarkup([//ini buat menampilkan Keyboard
		'keyboard' => $keyboard,
		'resize_keyboard' => true,
		// 'one_time_keyboard' => true//ini biar keyboardnya tampil sekali aja
		]);

		$response = Telegram::sendMessage([
		'chat_id' => $chatid,
		'text' => 'Silakan pilih menu yang anda inginkan ...',
		'reply_markup' => $reply_markup
		]);
		// $messageId = $response->getMessageId();//ini buat balas-balasan
	}//ini akhir dari fungsi tampil keyboard

	public function hideKeyboard($chatid)//buat ngilangin keyboard
	{//awal fungsi
		$reply_markup = Telegram::replyKeyboardHide();

		$response = Telegram::sendMessage([
			'chat_id' => $chatid,
			'text' => 'Terima Kasih sudah mencoba bot kami',
			'reply_markup' => $reply_markup
		]);
}//akhir fungsi
	// public function getReply($chatid, $text)//ini fungsi buat ngeget reply
	// {//ini awal fungsi
	// 	if ($text===) {
	// 		# code...
	// 	}
	// }//ini akhir fungsi

	// public function showMenuButton($chatid)//ini untuk buat fungsi showMenu, MASIH BELUM BISA MERESPON
	// {//ini awal fungsi showMenu
	// 	// this will create keyboard buttons for users to touch instead of typing commands
	// 	$inlineLayout = [[
	// 		Keyboard::inlineButton(['text' => 'Website', 'callback_data' => 'website']),
	// 		Keyboard::inlineButton(['text' => 'Contact', 'callback_data' => 'contact'])
	// 	]];
  //
	// 	// create an instance of the replyKeyboardMarkup method
	// 	$keyboard = Telegram::replyKeyboardMarkup([
	// 		'inline_keyboard' => $inlineLayout
	// 	]);
  //
	// 	// Now send the message with they keyboard using 'reply_markup' parameter
	// 	$response = Telegram::sendMessage([
	// 		'chat_id' => $chatid,
	// 		'text' => 'Keyboard',
	// 		'reply_markup' => $keyboard
	// 	]);
	// }//ini akhir fungsi

	public function showWebsite($chatid)
	{//awal dari fungsi Website
		$message = "Silakan hubungi mbah google.com";
		$response = Telegram::sendMessage([
		'chat_id' => $chatid,
		'text' => $message
		]);
	}//akhir fungsi website

	public function showContact($chatid)
	{//awal fungsi showContact
		$message = "Silakan hubungi admin kami pada @irfanprabaswara";
		$response = Telegram::sendMessage([
				'chat_id' => $chatid,
				'text' => $message
		]);
	}//akhir fungsi showContact

	public function showTiket($chatid)
	{//awal dari fungsi
		$message="";
		$result = DB::table('pemesanan')->where(['status'=>""])->get();
		$message = "*DAFTAR TIKET YANG BELUM TERKONFIRMASI* \n\n";
		if ($result->count()>0){
			for ($i=0;$i<$result->count();$i++){
				$message .= "*NOMOR TIKET =".$result[$i]->nomer_tiket."*\n";
				if($result[$i]->status ==""){
				$message .= "Status : Belum Terkonfirmasi\n";
				}
			$message .= "\n";
			}
		}
		$response = Telegram::sendMessage([
			'chat_id' => $chatid,
			'parse_mode' => 'markdown',
			'text' => $message
		]);
	}//akhir dari fungsi

	public function showDriverList($chatid)
	{//untuk menampilkan Driver beserta statusnya
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

	public function updateDriver()//fungsi untuk mengupdate driver
	{//awal fungsi

	}//akhir fungsi
}//akhir kelas

?>
