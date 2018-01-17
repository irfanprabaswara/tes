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

class tes extends Controller
{
  public function webhook()
  {
		// try{
			$request = Telegram::getWebhookUpdates();

			// if($request->isType('callback_query')){
			// 	$query = $request->getCallbackQuery();
			// 	$text = $query->getData();
			// 	$chatid = $query->getMessage()->getChat()->getId();
			// 	$messageid = $query->getMessage()->getMessageId();
			// 	$callback_query_id = $query->getId();
			// }else{
				$chatid = $request->getMessage()->getChat()->getId();
				$text = $request->getMessage()->getText();
				$callback_query_id = 0;
			// }

			switch($text) {
				case $text === '/start':
					$this->showWelcomeMessage($chatid);
					break;
				case $text === '/website':
				  $this->showWebsite($chatid);
					break;
				// case $text === '/contact':
				//    $this->showContact($chatid, $callback_query_id);
				//    break;
				case $text === '/driver':
					$this->showDriverList($chatid);
					break;
			// // 	case $text === '/updatedriver':
			// 		$this->showUpdateDriver($chatid);
			// 		break;
			// 	case substr($text,0,7) === '/upddrv':
			// 		$listparams = substr($text,7);
			// 		$params = explode('#',$listparams);
			// 		unset($params[0]);
			// 		$params = array_values($params);
      //
			// 		if(count($params)==1){
			// 			$this->confirmDriver($chatid, $params);
			// 		}elseif(count($params)==2){
			// 			if($params[1]=="set"){
			// 				$this->setPic($chatid, $params);
			// 			}else{
			// 				$this->releaseDriver($chatid, $params);
			// 			}
			// 		}elseif(count($params)==3){
			// 			// $callback_query_id=0;
			// 			$month_input = date("Y-m");
			// 			$this->showCalendar($chatid, $params, $month_input, $callback_query_id);
			// 		}elseif(count($params)==4){
			// 			// $callback_query_id=0;
			// 			$this->setLocation($chatid, $params);
			// 		}elseif(count($params)==5){
			// 			// $callback_query_id=0;
			// 			$this->saveTheUpdates($chatid, $params);
			// 		}
      //
			// 		//$response_txt .= "Mengenal command dan berhasil merespon\n";
			// 		break;
			// 	// case $text === '/calendar':
			// 		// $month_input = date("Y-m");
			// 		// $callback_query_id=0;
			// 		// $this->showCalendar($chatid, $month_input, $callback_query_id);
			// 		// //$response_txt .= "Mengenal command dan berhasil merespon\n";
			// 		// break;
			// 	case substr($text,0,6) === 'change':
			// 		$month_input = substr($text,6,7);
			// 		$this->changeCalendar($chatid, $messageid, $month_input, $callback_query_id);
			// 		break;
				default:
				   $info = 'I do not understand what you just said. Please choose an option';
           $this->showWelcomeMessage($chatid);
				   // $this->showMenu($chatid, $info);
				   break;
			}
		// }catch(Exception $e){
		// 	//PERLU DIPERHATIKAN CHAT ID 437329516 punya kanwil 67409805
		// 	Telegram::sendMessage([
		// 		'chat_id' => 437329516,
		// 		'text' => "Reply ".$e->getMessage()
		// 	]);
		// }
		// return 'ok';
	}

  // public function showMenu($chatid, $info = null){
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
	// }

  public function showWelcomeMessage($chatid){
		$message = "Sugeng rawuh. Bot Kanwil Yogya siap membantu seadanya";
		$response = Telegram::sendMessage([
			'chat_id' => $chatid,
			'parse_mode' => 'markdown',
			'text' => $message
		]);
	}
  //
  public function showWebsite($chatid,  $callback_query_id)
  {
	    // if($cbid != 0){
			// $responses = Telegram::answerCallbackQuery([
			// 	'callback_query_id' => $cbid,
			// 	'text' => '',
			// 	'show_alert' => false
			// ]);
		$message = 'https://jqueryajaxphp.com';

		$response = Telegram::sendMessage([
			'chat_id' => $chatid,
			'text' => $message
		]);
	}

	// public function showContact($chatid, $callback_query_id){
	// 	if($cbid != 0){
	// 		$responses = Telegram::answerCallbackQuery([
	// 			'callback_query_id' => $cbid,
	// 			'text' => '',
	// 			'show_alert' => false
	// 		]);
	// 	}

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

}

?>
