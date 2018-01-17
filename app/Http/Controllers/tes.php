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


class Tes extends Controller
{
  public function showWelcomeMessage($chatid){
		$message = "Sugeng rawuh. Bot Kanwil Yogya siap membantu seadanya";
		$response = Telegram::sendMessage([
			'chat_id' => $chatid,
			'parse_mode' => 'markdown',
			'text' => $message
		]);
	}

  public function showDriverList($chatid){
		$message="";
		$result = DB::table('driver')->get();
		$message = "*DAFTAR DRIVER KANWIL* \n\n";
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
	}

  public function showMenu($chatid, $info = null){
		// this will create keyboard buttons for users to touch instead of typing commands
		$inlineLayout = [[
			Keyboard::inlineButton(['text' => 'Website', 'callback_data' => 'website']),
			Keyboard::inlineButton(['text' => 'Contact', 'callback_data' => 'contact'])
		]];

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
	}

  public function showWebsite($chatid, $cbid){
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
	}

	public function showContact($chatid, $cbid){
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
	}

  public function respond()
  {
    $telegram = new Api(env('TELEGRAM_BOT_TOKEN'));
    $response = $telegram->getUpdates();
    $request = collect(end($response));

    $chatid = $request['message']['chat']['id'];
    $text = $request['message']['text'];

    switch($text) {
     case '/start':
       $this->showWelcomeMessage($chatid);
     break;
     case '/daftardriver':
       $this->showDriverList($chatid);
     break;
     case '/showmenu':
       $this->showMenu($chatid, $info = null);
     break;
     case '/contact';
       $this->showContact($chatid);
     break;
     case substr($text,0,7) === '/upddrv':
				$listparams = substr($text,7);
				$params = explode('#',$listparams);
				unset($params[0]);
				$params = array_values($params);

				if(count($params)==1){
					$this->confirmDriver($chatid, $params);
				}elseif(count($params)==2){
					if($params[1]=="set"){
						$this->setPic($chatid, $params);
					}else{
						$this->releaseDriver($chatid, $params);
					}
				}elseif(count($params)==3){
					// $callback_query_id=0;
					$month_input = date("Y-m");
					$this->showCalendar($chatid, $params, $month_input, $callback_query_id);
				}elseif(count($params)==4){
					// $callback_query_id=0;
					$this->saveTheUpdates($chatid, $params);
				}
				//$response_txt .= "Mengenal command dan berhasil merespon\n";
				break;

     default:
       $info = 'I do not understand what you just said. Please choose an option';
       $this->showMenu($chatid, $info);
     }
  }













//   public function respond()
//   {
//     $telegram = new Api (env('TELEGRAM_BOT_TOKEN'));
//     $request = Telegram::getUpdates();
//     $request = collect(end($request));
//     $callback_query_id = 0;
//     $chatid = $request['message']['chat']['id'];
//     $text = $request['message']['text'];
// // if($request->isType('callback_query')){
// //   $query = $request->getCallbackQuery();
// //   $text = $query->getData();
// //   $chatid = $query->getMessage()->getChat()->getId();
// //   $callback_query_id = $query->getId();
// // }else{
// //   $chatid = $request->getMessage()->getChat()->getId();
// //   $text = $request->getMessage()->getText();
// //   $callback_query_id = 0;
// }
//     switch($text)
//     {
//       case ($text) === '/welcome';
//         $this-> showWelcomeMessage($chatid);
//       break;
//       case $text === '/calendar':
//         $month_input = date("Y-m");
//         $callback_query_id=0;
//         $this->showCalendar($chatid, $month_input, $callback_query_id);
//       //$response_txt .= "Mengenal command dan berhasil merespon\n";
//       break;
//       case $text === '/updatedriver':
//         $this->showUpdateDriver($chatid);
//       //$response_txt .= "Mengenal command dan berhasil merespon\n";
//       break;
//       case substr($text,0,7) === '/upddrv':
//         $listparams = substr($text,7);
//         $params = explode('#',$listparams);
//         unset($params[0]);
//         $params = array_values($params);
//
//         if(count($params)==1){
//           $this->confirmDriver($chatid, $params);
//         }elseif(count($params)==2){
//         if($params[1]=="set"){
//           $this->setPic($chatid, $params);
//         }else{
//           $this->releaseDriver($chatid, $params);
//         }
//         }elseif(count($params)==3){
//         // $callback_query_id=0;
//         $month_input = date("Y-m");
//         $this->showCalendar($chatid, $params, $month_input, $callback_query_id);
//         }elseif(count($params)==4){
//         // $callback_query_id=0;
//         $this->saveTheUpdates($chatid, $params);
//         }
//       //$response_txt .= "Mengenal command dan berhasil merespon\n";
//       break;
//     // case $text === '/calendar':
//       // $month_input = date("Y-m");
//       // $callback_query_id=0;
//       // $this->showCalendar($chatid, $month_input, $callback_query_id);
//       // //$response_txt .= "Mengenal command dan berhasil merespon\n";
//       // break;
//       case substr($text,0,6) === 'change':
//         $month_input = substr($text,6,7);
//         $this->changeCalendar($chatid, $messageid, $month_input, $callback_query_id);
//       break;
//       default:
//        $info = 'I do not understand what you just said. Please choose an option';
//        $this->showMenu($chatid, $info);
//       break;
//     }
//       return $request;
//   }
}
?>
