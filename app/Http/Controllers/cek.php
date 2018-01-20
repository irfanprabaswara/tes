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

class cek extends Controller
{//awal kelas

	public function setWebhook(){
		$response = Telegram::setWebhook(['url' => 'https://caa49752.ngrok.io/tes/public/webhook',]);
		dd($response);
	}

	public function unsetWebhook()
	{
		$response = Telegram::removeWebhook();
		dd($response);
	}

	public function showMenu($chatid, $info = null){
    $message = '';
    if($info !== null){
        $message .= $info.chr(10);
    }
    $message .=  '/website'.chr(10);
    $message .= '/contact'.chr(10);

    $response = Telegram::sendMessage([
        'chat_id' => $chatid,
        'text' => $message
    ]);
	}

	public function showWebsite($chatid){
    $message = 'http://google.com';

    $response = Telegram::sendMessage([
        'chat_id' => $chatid,
        'text' => $message
    ]);
	}

	public function showContact($chatid){
    $message = 'info@jqueryajaxphp.com';

    $response = Telegram::sendMessage([
        'chat_id' => $chatid,
        'text' => $message
    ]);
	}

	public function webhook()
  {//awal fungsi webhook
		try{//awal try
			$request = Telegram::getWebhookUpdates();

			if($request->isType('callback_query')){
				$query = $request->getCallbackQuery();
				$text = $query->getData();
				$chatid = $query->getMessage()->getChat()->getId();
				$messageid = $query->getMessage()->getMessageId();
				$callback_query_id = $query->getId();
			}else{//mulai else
				$chatid = $request->getMessage()->getChat()->getId();
				$text = $request->getMessage()->getText();
				$callback_query_id = 0;
			}//end else

			switch($text)
      {//mulai switch
				case $text === '/start':
					$this->showWelcomeMessage($chatid);
					break;
				case $text === 'website':
				   $this->showWebsite($chatid, $callback_query_id);
					   break;
				case $text === 'contact':
				   $this->showContact($chatid, $callback_query_id);
				   break;
				case $text === '/driver':
					$this->showDriverList($chatid);
					break;
				case $text === '/updatedriver':
					$this->showUpdateDriver($chatid);
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
						$this->setLocation($chatid, $params);
					}elseif(count($params)==5){
						// $callback_query_id=0;
						$this->saveTheUpdates($chatid, $params);
					}

					//$response_txt .= "Mengenal command dan berhasil merespon\n";
					break;

				case substr($text,0,6) === 'change':
					$month_input = substr($text,6,7);
					$this->changeCalendar($chatid, $messageid, $month_input, $callback_query_id);
					break;
				default:
				   $info = 'I do not understand what you just said. Please choose an option';
				   $this->showMenu($chatid, $info);
				   break;
			}//end switch
		}//end try
	}

	public function showWelcomeMessage($chatid)
  {//awal fungsi
		$message = "Sugeng rawuh. Bot Kanwil Yogya siap membantu seadanya";
		$response = Telegram::sendMessage([
			'chat_id' => $chatid,
			'parse_mode' => 'markdown',
			'text' => $message
		]);
	}//akhir fungsi

	public function showDriverList($chatid)//fungsi buat nampilin data driver
  {//awal fungsi
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
			}//end for
		}//end if

		$response = Telegram::sendMessage([
			'chat_id' => $chatid,
			'parse_mode' => 'markdown',
			'text' => $message
		]);
	}//akhir fungsi

	public function showMenu($chatid, $info = null)//fungsi buat nampilin menu
  {//awal fungsi
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
	}//akhir fungsi

	public function showWebsite($chatid, $cbid)//buat nampilin website
  {//awal fungsi
	    if($cbid != 0){
			$responses = Telegram::answerCallbackQuery([
				'callback_query_id' => $cbid,
				'text' => '',
				'show_alert' => false
			]);
    }end if
		$message = 'https://jqueryajaxphp.com';

		$response = Telegram::sendMessage([
			'chat_id' => $chatid,
			'text' => $message
		]);
	}//akhir fungsi

	public function showContact($chatid, $cbid)//fungsi buat nampilin contact
  {//awal fungsi
		if($cbid != 0){
			$responses = Telegram::answerCallbackQuery([
				'callback_query_id' => $cbid,
				'text' => '',
				'show_alert' => false
			]);
		}//end if

		$message = 'silakah hubungi admin kami di @irfanprabaswara';

		$response = Telegram::sendMessage([
			'chat_id' => $chatid,
			'text' => $message
		]);
	}//akhir fungsi

	public function showUpdateDriver($chatid)//fungsi buat update driver
  {//awal fungsi
		$driver = [];
		$keyboard = [];
		$message="";
		$result = DB::table('driver')->get();
		$message = "*PILIH DRIVER YANG AKAN DI-UPDATE* \n\n";
		$max_col = 3;
		$col =0;
		if ($result->count()>0){
			for ($i=0;$i<$result->count();$i++){
				if($col<$max_col){
					$driverperrow[] = Keyboard::inlineButton(['text' => $result[$i]->nama, 'callback_data' => '/upddrv#'.$result[$i]->Id]);
				}else{
					$col=0;
					$driver[] = $driverperrow;
					$driverperrow = [];
					$driverperrow[] = Keyboard::inlineButton(['text' => $result[$i]->nama, 'callback_data' => '/upddrv#'.$result[$i]->Id]);
				}//end else
				$col++;
			}//end for
		}//end if
		if($col>0){
			$col=0;
			$driver[] = $driverperrow;
		}//end if

		$reply_markup = Telegram::replyKeyboardMarkup([
			'resize_keyboard' => true,
			'one_time_keyboard' => true,
		    'inline_keyboard' => $driver
		]);

		$response = Telegram::sendMessage([
		  'chat_id' => $chatid,
		  'parse_mode' => 'markdown',
		  'text' => $message,
		  'reply_markup' => $reply_markup
		]);
	}//akhir fungsi

	public function confirmDriver($chatid, $params)
  {//awal fungsi
		$message="";
		$keyboard = [];
		$driverid = $params[0];
		$result = DB::table('driver')->where(['id'=>$driverid])->get();
		if ($result->count()>0){
			$message .= "Driver ".$result[0]->nama." ";
			if($result[0]->status == "Terpakai"){
				$message .= "saat ini sedang bertugas";
				$keyboardperrow[] = Keyboard::inlineButton(['text' => 'Selesai Bertugas?', 'callback_data' => '/upddrv#'.$params[0]."#release"]);
			}else{
				$message .= "saat ini kosong \n";
				$keyboardperrow[] = Keyboard::inlineButton(['text' => 'Set Penugasan?', 'callback_data' => '/upddrv#'.$params[0]."#set"]);
			}//akhir else
			$keyboard[] = $keyboardperrow;
			$reply_markup = Telegram::replyKeyboardMarkup([
				'resize_keyboard' => true,
				'one_time_keyboard' => true,
				'inline_keyboard' => $keyboard
			]);
			$response = Telegram::sendMessage([
				'chat_id' => $chatid,
				'text' => $message,
				'reply_markup' => $reply_markup
			]);
		}//akhir if
    else{
			$response = Telegram::sendMessage([
				'chat_id' => $chatid,
				'text' => "Data Driver salah"
			]);
		}//akhir else
		$messageId = $response->getMessageId();
	}//akhir fungsi

	public function releaseDriver($chatid, $params)
  {//awal fungsi
		$result = DB::table('driver')->where(['Id'=>$params[0]])->update(['status'=>""]);
		$message = "Data Driver berhasil terupdate\n";
		$response = Telegram::sendMessage([
			'chat_id' => $chatid,
			'text' => $message
		]);
	}//akhir fungsi

	public function setPic($chatid, $params)//fungsi buat milih bagian kerja atau PIC
  {//awal fungsi
		$message="";
		$pic = [];
		$driverid = $params[0];
		$piclist = ['LOG','SDM','MRK','LEGAL','OJL','ECH','KONSUMER','AO','BIT','ARK','ADK','RPKB','EBK','PRG','DJS','BRILINK','RTL','MKR','WPO','WPB1','WPB2','WPB3','WPB4','PINWIL','KANPUS','PIHAK LUAR','LAIN-LAIN'];
		$message = "*PILIH PIC YANG PESAN* \n\n";
		$max_col = 4;
		$col =0;
		for ($i=0;$i<count($piclist);$i++){
			if($col<$max_col){
				$picperrow[] = Keyboard::inlineButton(['text' => $piclist[$i], 'callback_data' => '/upddrv#'.$params[0]."#".$params[1]."#".$piclist[$i]]);
			}else{
				$col=0;
				$pic[] = $picperrow;
				$picperrow = [];
				$picperrow[] = Keyboard::inlineButton(['text' => $piclist[$i], 'callback_data' => '/upddrv#'.$params[0]."#".$params[1]."#".$piclist[$i]]);
			}//end else
			$col++;
		}//end for
		if($col>0){
			$col=0;
			$pic[] = $picperrow;
		}//end if
		$reply_markup = Telegram::replyKeyboardMarkup([
			'resize_keyboard' => true,
			'one_time_keyboard' => true,
		    'inline_keyboard' => $pic
		]);

		$response = Telegram::sendMessage([
		  'chat_id' => $chatid,
		  'parse_mode' => 'markdown',
		  'text' => $message,
		  'reply_markup' => $reply_markup
		]);
	}//akhir fungsi

	public function showCalendar($chatid, $params, $month_input, $cbid)
  {//awal fungsi
		if($cbid != 0){
			$responses = Telegram::answerCallbackQuery([
				'callback_query_id' => $cbid,
				'text' => '',
				'show_alert' => false
			]);
		}//end if

		$message = "*PILIH TANGGAL PENUGASAN*\n";
		$message .= DateTime::createFromFormat('Y-m-d',$month_input."-01")->format("F Y")." \n";
		$calendar = $this->createCalendar($month_input, $params);

		$reply_markup = Telegram::replyKeyboardMarkup([
			'resize_keyboard' => true,
			'one_time_keyboard' => true,
		    'inline_keyboard' => $calendar
		]);

		$response = Telegram::sendMessage([
		  'chat_id' => $chatid,
		  'text' => $message,
		  'parse_mode' => 'markdown',
		  'reply_markup' => $reply_markup
		]);
	}//akhir fungsi

	public function changeCalendar($chatid, $messageid, $month_input, $cbid)
  {//awal fungsi
		if($cbid != 0){
			$responses = Telegram::answerCallbackQuery([
				'callback_query_id' => $cbid,
				'text' => '',
				'show_alert' => false
			]);
		}//end if

		$message = "";
		$message .= DateTime::createFromFormat('Y-m-d',$month_input."-01")->format("F Y")." \n";
		$calendar = $this->createCalendar($month_input);

		$reply_markup = Telegram::replyKeyboardMarkup([
			'resize_keyboard' => true,
			'one_time_keyboard' => true,
		    'inline_keyboard' => $calendar
		]);

		$response = Telegram::editMessageText([
		  'chat_id' => $chatid,
		  'message_id' =>$messageid,
		  'text' => $message,
		  'reply_markup' => $reply_markup
		]);
	}//akhir fungsi

	public function createCalendar($month_input, $params)//fungsi buat bikin kalender
  {//awal fungsi
		$calendar = [];
		$keyboard = [];
		$maxdate = date("t", strtotime($month_input."-01"));
		$startday = date("w", strtotime($month_input."-01"));
		$date = 1;
		$row = 0;
		$calendar = [];
		while($date<=$maxdate){
			$calendarperrow = [];
			for($col=0;$col<7;$col++){
				if((($col<$startday)&&($row==0))||(($date>$maxdate))){
					$calendarperrow[] = Keyboard::inlineButton(['text' => '_', 'callback_data' => '_']);
				}else{
					$calendarperrow[] = Keyboard::inlineButton(['text' => substr("0".strval($date),-2), 'callback_data' => '/upddrv#'.$params[0]."#".$params[1]."#".$params[2]."#".$month_input."-".substr("0".strval($date),-2)]);
					$date++;
				}//end else
			}//end for
			$calendar[] = $calendarperrow;
			$row++;
		}//end while

		$eek = trim($month_input)."-01";
		$prev_date = DateTime::createFromFormat('Y-m-d',$eek)->sub(new DateInterval('P1M'))->format("Y-m");
		$next_date = DateTime::createFromFormat('Y-m-d',$eek)->add(new DateInterval('P1M'))->format("Y-m");

		$calendarperrow = [
			Keyboard::inlineButton(['text' => 'Previous', 'callback_data' => "change".$prev_date]),
			Keyboard::inlineButton(['text' => 'Next', 'callback_data' => "change".$next_date])
		];
		$calendar[] = $calendarperrow;

		return $calendar;
	}//akhir fungsi

	public function setLocation($chatid, $params)//fungsi buat milih tujuan kerja
  {//awal fungsi
		$message="";
		$location = [];
		$driverid = $params[0];
		$locationlist = ['DALAM KOTA', 'LUAR KOTA'];
		$message = "*PILIH LOKASI PENUGASAN* \n\n";
		$max_col = 4;
		$col =0;
		for ($i=0;$i<count($locationlist);$i++){
			if($col<$max_col){
				$locationperrow[] = Keyboard::inlineButton(['text' => $locationlist[$i], 'callback_data' => '/upddrv#'.$params[0]."#".$params[1]."#".$params[2]."#".$params[3]."#".$locationlist[$i]]);
			}else{
				$col=0;
				$location[] = $locationperrow;
				$location = [];
				$location[] = Keyboard::inlineButton(['text' => $locationlist[$i], 'callback_data' => '/upddrv#'.$params[0]."#".$params[1]."#".$params[2]."#".$params[3]."#".$locationlist[$i]]);
			}//end else
			$col++;
		}//end for
		if($col>0){
			$col=0;
			$location[] = $locationperrow;
		}//end if
		$reply_markup = Telegram::replyKeyboardMarkup([
			'resize_keyboard' => true,
			'one_time_keyboard' => true,
		    'inline_keyboard' => $location
		]);

		$response = Telegram::sendMessage([
		  'chat_id' => $chatid,
		  'parse_mode' => 'markdown',
		  'text' => $message,
		  'reply_markup' => $reply_markup
		]);
	}//akhir fungsi

	public function saveTheUpdates($chatid, $params)
  {//awal fungsi
		$status="";
		if($params[1]=="set"){
			$status= "Terpakai";
		}
		$result = DB::table('log_driver')->insert(['tanggal'=>date('Y-m-d H:i:s'),'Id'=>$params[0],'pic'=>$params[2],'tanggal_mulai'=>$params[3], 'lokasi'=>$params[4]]);
		$result = DB::table('driver')->where(['Id'=>$params[0]])->update(['status'=>$status]);
		$message = "Data Driver berhasil terupdate\n";
		$response = Telegram::sendMessage([
			'chat_id' => $chatid,
			'text' => $message
		]);
	}//akhir fungsi

}//akhir kelas
?>
