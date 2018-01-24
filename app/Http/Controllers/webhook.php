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
		$response = Telegram::setWebhook(['url' => 'https://49a6dd1b.ngrok.io/tes/public/webhook',]);
		dd($response);
	}

	public function unsetWebhook()
	{
		$response = Telegram::removeWebhook();
		dd($response);
	}

	function webhook()
	{//awal func webhook
		try {
			$request = Telegram::getWebhookUpdates();//buat ngeget chat

		if(isset($request['callback_query'])){//buat ngecek apakah yg terbaru itu jenis callback query atau bukan
      $text = $request['callback_query']['data'];
      $chatid = $request['callback_query']['message']['chat']['id'];
			$chatid = $request['callback_query']['message']['chat']['username'];
      $callback_query_id = $request['callback_query']['id'];
    }else{//buat kasus $request bukan callback_query
      $text = $request['message']['text'];
      $chatid = $request['message']['chat']['id'];
			$username = $request['message']['chat']['username'];
      $callback_query_id = 0;
    }//end else


		$keyboard = [//ini buat bikin keyboard menu
				['/driver'],['/start'],['/website'],['/contact'],['/tiket'],['/hideKeyboard'],['/pesandriver']
		];//ini akhir dari keyboard
		$keypic=[//ini buat keyboard pic
				['LOG','SDM','MRK','LEGAL'],
				['OJL','ECH','KONSUMER','AO'],
				['BIT','ARK','ADK','RPKB'],
				['EBK','PRG','DJS','BRILINK'],
				['RTL','MKR','WPO','WPB1'],
				['WPB2','WPB3','WPB4','PINWIL'],
				['KANPUS','PIHAK LUAR','LAIN-LAIN']
		];//ini akhir dari keyboard PIC

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
				case $text==='/tiket':
					$this->showTiket($chatid);
					break;
				case $text==='/pesandriver':
					$this->pesanDriver($chatid, $keypic);
					break;
				case $text==='/latihan':
					$this->showMenu($chatid);
					break;
				case $text==='website':
					$this->showWebsite($chatid, $callback_query_id);
					break;
				case $text==='contact':
					$this->showContact($chatid, $callback_query_id);
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
		} catch (\Exception $e) {
			Telegram::sendMessage([
				'chat_id' => 437329516,
				'text' => "Reply ".$e->getMessage()
			]);
		}//end catch
		return 'ok';
	}//akhir function webhook

	public function showMenu($chatid)
  {//awal fungsi
 		// this will create keyboard buttons for users to touch instead of typing commands
 		$inlineLayout = [
 			 [
 					 Keyboard::inlineButton(['text' => 'Website', 'callback_data' => 'website']),
 					 Keyboard::inlineButton(['text' => 'Contact', 'callback_data' => 'contact'])
 					 // Keyboard::inlineButton(['text' => 'Driver', 'callback_data' => 'driver'])
 			 ]
 	 ];
 	 // create an instance of the replyKeyboardMarkup method
 	 $keyboard = Telegram::replyKeyboardMarkup([
 			 'inline_keyboard' => $inlineLayout
 	 ]);
 	 // Now send the message with they keyboard using 'reply_markup' parameter
 	 $response = Telegram::sendMessage([
 			 'chat_id' => $chatid,
 			 'text' => 'Keyboard',
 			 'reply_markup' => $keyboard
 	 ]);
  }//akhir fungsi menu

 	public function showWebsite($chatid, $cbid)
 	{//awal fungsi website
    if($cbid != 0){
         $responses = Telegram::answerCallbackQuery([
             'callback_query_id' => $cbid,
             'text' => '',
             'show_alert' => false
         ]);
     }
     $message = 'https://jqueryajaxphp.com';

     $response = Telegram::sendMessage([
         'chat_id' => $chatid,
         'text' => $message
     ]);
 	}//akhir fungsi Website
 //
 public function showContact($chatid, $cbid)
 {//awal fungsi kontak
     if($cbid != 0){
         $responses = Telegram::answerCallbackQuery([
             'callback_query_id' => $cbid,
             'text' => '',
             'show_alert' => false
         ]);
     }

     $message = 'info@jqueryajaxphp.com';

     $response = Telegram::sendMessage([
         'chat_id' => $chatid,
         'text' => $message
     ]);
 	}//akhir fungsi kontak

	public function pesanDriver($chatid, $keypic) //ini fungsi memesan driver
	{//ini awal fungsi
		$reply_markup = Telegram::replyKeyboardMarkup([//ini buat menampilkan Keyboard
		'keyboard' => $keypic,
		'resize_keyboard' => true,
		'one_time_keyboard' => true//ini biar keyboardnya tampil sekali aja
		]);

		$response = Telegram::sendMessage([
		'chat_id' => $chatid,
		'text' => 'Silakan pilih bagian kerja anda ...',
		'reply_markup' => $reply_markup
		]);

		// $this->temp1($chatid,$text)
	}//ini akhir fungsi

	// public function temp1($chat_id,$text)
	// {//ini awal fungsi
	// 	$temp=$text;
	// 	$message= "Bagian anda adalah : ".$temp;
	// 	$response=Telegram::sendMessage([
	// 		'chat_id'=>$chatid,
	// 		'text'=>$message
	// 	]);
	// }//ini akhir fungsi

	public function showWelcomeMessage($chatid)//ini untuk menampilkan pesan selamat datang
  {//ini awal fungsi
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
		'one_time_keyboard' => true//ini biar keyboardnya tampil sekali aja
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


	// $reply_markup = Telegram::replyKeyboardMarkup([//ini buat menampilkan Keyboard
	// 'keyboard' => $keyboard,
	// 'resize_keyboard' => true,
	// 'one_time_keyboard' => true//ini biar keyboardnya tampil sekali aja
	// ]);
  //
	// $response = Telegram::sendMessage([
	// 'chat_id' => $chatid,
	// 'text' => 'Silakan pilih menu yang anda inginkan ...',
	// 'reply_markup' => $reply_markup
	// ]);

	// public function showTiketKeyboard($chatid)
	// {//awal dari fungsi
	// 	$tiket=[];
	// 	$keytiket = [];
	// 	$tes=[];
	// 	$message="";
	// 	$result = DB::table('pemesanan')->where(['status'=>""])->get();
	// 	$message = "*PILIH TIKET YANG AKAN DIPROSES ...* \n\n";
	// 	$max_col = 3;
	// 	$col =0;
  //
	// 	if ($result->count()>0){
	// 		for ($i=0;$i<$result->count();$i++){
	// 			$keytiket[] = $tes[['text' => $result[$i]->nama]];
	// 			}//end else
	// 			$col++;
	// 		}//end for
	// 	}//end if
	// 	if($col>0){
	// 		$col=0;
	// 		$tiket[] = $keytiket;
	// 	}//end if
  //
	// 	$reply_markup = Telegram::replyKeyboardMarkup([
	// 		'resize_keyboard' => true,
	// 		'one_time_keyboard' => true,
	// 	  'inline_keyboard' => $tiket
	// 	]);
  //
	// 	$response = Telegram::sendMessage([
	// 	  'chat_id' => $chatid,
	// 	  'parse_mode' => 'markdown',
	// 	  'text' => $message,
	// 	  'reply_markup' => $reply_markup
	// 	]);
	// }//akhir dari fungsi

	public function showTiket($chatid)
	{//awal dari fungsi
		$message="";
		$result = DB::table('pemesanan')->where(['status'=>""])->get();
		$message = "*DAFTAR TIKET YANG BELUM TERKONFIRMASI* \n\n";
		if ($result->count()>0){
			for ($i=0;$i<$result->count();$i++){
				$message .= "*NOMOR TIKET =".$result[$i]->nomer_tiket."*\n";
			$message .= "\n";
			}//end for
		}//end if
		$response = Telegram::sendMessage([
			'chat_id' => $chatid,
			'parse_mode' => 'markdown',
			'text' => $message
		]);
	}//akhir dari fungsi

	public function showDriverList($chatid)//buat bikin fungsi showDriverList
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

	// public function updateDriver()//fungsi untuk mengupdate driver
	// {//awal fungsi
  //
	// }//akhir fungsi
}//akhir kelas

?>
