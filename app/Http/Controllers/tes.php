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
//PERLU DIPERHATIKAN

class tes extends Controller
{//awal kelas
	public function respond()
	{//awal fungsi respond
	$telegram = new Api (env('TELEGRAM_BOT_TOKEN'));
	$request = Telegram::getUpdates();
	$request = collect(end($request));
	$callback_query_id = 0;
	$chatid = $request['message']['chat']['id'];
	$text = $request['message']['text'];

	switch($text) {
		case '/start':
				$this->showMenu($chatid);
				break;
		case '/menu':
				$this->showMenu($chatid);
				break;
		case 'website':
			 $this->showWebsite($chatid, $callback_query_id);
				break;
		case 'contact';
			 $this->showContact($chatid, $callback_query_id);
			 break;
		default:
			 $info = 'I do not understand what you just said. Please choose an option';
			 $this->showMenu($chatid, $info);
		 }//akhir switch
	}//akhir fungsi respond

	public function webhook(Request $request)
	{//awal fungsi webhook

    if(isset($request['callback_query'])){
      $text = $request['callback_query']['data'];
      $chatid = $request['callback_query']['message']['chat']['id'];
      $callback_query_id = $request['callback_query']['id'];
    }else{
      $text = $request['message']['text'];
      $chatid = $request['message']['chat']['id'];
      $callback_query_id = 0;
    }

    switch($text) {
	case '/start':
	    $this->showMenu($chatid);
	    break;
	case '/menu':
	    $this->showMenu($chatid);
	    break;
	case 'website':
	   	$this->showWebsite($chatid, $callback_query_id);
      break;
	case 'contact';
	   $this->showContact($chatid, $callback_query_id);
	   break;
	// case 'driver':
	//    $this->showDriverList($chatid, $callback_query_id);
	//    break;

	default:
	   $info = 'I do not understand what you just said. Please choose an option';
	   $this->showMenu($chatid, $info);
   }
 }//akhir fungsi webhook

 public function showMenu($chatid, $info=null)
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

	// public function showDriverList($chatid, $cbid)//buat bikin fungsi showDriverList
	// {//untuk menampilkan Driver beserta statusnya
	// 	if($cbid != 0){
  //        $responses = Telegram::answerCallbackQuery([
  //            'callback_query_id' => $cbid,
  //            'text' => '',
  //            'show_alert' => false
  //        ]);
  //    }
  //
	// 	$message="";
	// 	$result = DB::table('driver')->get();
	// 	$message = "*DAFTAR DRIVER KANWIL YOGYAKARTA* \n\n";
	// 	if ($result->count()>0){
	// 		for ($i=0;$i<$result->count();$i++){
	// 			$message .= "*".$result[$i]->nama."*\n";
	// 			if($result[$i]->status ==""){
	// 				$message .= "Status : Kosong\n";
	// 			}else{
	// 				$message .= "Status : ".$result[$i]->status."\n";
	// 			}
	// 			$message .= "\n";
	// 		}
	// 	}
  //
	// 	$response = Telegram::sendMessage([
	// 		'chat_id' => $chatid,
	// 		'parse_mode' => 'markdown',
	// 		'text' => $message
	// 	]);
	// }//ini akhir fungsi

}//akhir kelas

?>
