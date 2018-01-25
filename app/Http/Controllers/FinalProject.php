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

class FinalProject extends Controller
{//awal kelas

	public function respond()
  {
  $telegram = new Api (env('TELEGRAM_BOT_TOKEN'));
  $request = Telegram::getUpdates();
  $request = collect(end($request));

    $chatid = $request['message']['chat']['id'];
    $text = $request['message']['text'];
    $username=$request['message']['chat']['username'];


		switch($text)
		{//mulai switch
			case $text === '/start'://udah bisa
				$this->showWelcomeMessage($chatid);
				break;
			case $text==='/menu'://udah bisa
				$this->showMenu($chatid);
				break;
			case $text === 'website'://udah bisa
				 $this->showWebsite($chatid, $callback_query_id);
					 break;
			case $text === 'contact'://udah bisa
				 $this->showContact($chatid, $callback_query_id);
				 break;
			case $text === '/driver'://udah bisa
				$this->showDriverList($chatid, $username, $text);
				break;
			case $text === '/updatedriver'://Udah bisa
				$this->showUpdateDriver($chatid, $username, $text);
				break;
			case $text === '/pesandriver'://udah bisa
				$this->aturPic($chatid);
				break;
			case $text === '/updatetiket'://udah bisa
				$this->updateTiket($chatid, $text, $username);
				break;
			case $text === '/selesai'://BUAT CONFIRM DRIVER SELESAI BERTUGAS
				 $this->konfirmasi($chatid, $username, $text);
				 break;
			//BUAT CONFIRM DRIVER SELESAI BERTUGAS
			case substr($text,0,8) === '/confirm':
				 $listparams = substr($text,8);
				 $params = explode('#',$listparams);
				 unset($params[0]);
				 $params = array_values($params);

					if(count($params)==1){
						$this->updateStatusDriver($chatid, $params);
					}
				break;
			//BUAT UPDATE TIKET
			case substr($text,0,7) === '/updtkt':
				$listparams = substr($text,7);
				$params = explode('#',$listparams);
				unset($params[0]);
				$params = array_values($params);

				if(count($params)==1){
					$this->showDataTiket($chatid, $params);
					$this->setDriver($chatid, $params);
				}else{
					$this->updateLog($chatid, $params);
				}
			break;
			//BUAT PESAN DRIVER
			case substr($text,0,7) === '/psndrv':
				$listparams = substr($text,7);
				$params = explode('#',$listparams);
				unset($params[0]);
				$params = array_values($params);

				if(count($params)==1){
					$month_input = date("Y-m");
					$this->tampilCalendar($chatid, $params, $month_input, $callback_query_id);
				}elseif(count($params)==2){
					$this->lokasi($chatid, $params);
				}elseif(count($params)==3){
					$this->simpanPesanan($chatid, $params, $username);
				}
				//$response_txt .= "Mengenal command dan berhasil merespon\n";
				break;
			//BUAT UPDATE DRIVER
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
					$this->saveTheUpdates($chatid, $params, $username);
				}//end elseif

				//$response_txt .= "Mengenal command dan berhasil merespon\n";
				break;

			case substr($text,0,6) === 'change':
				$month_input = substr($text,6,7);
				$this->changeCalendar($chatid, $messageid, $month_input, $callback_query_id);
				break;
			case substr($text,0,4) === 'ubah':
				$month_input = substr($text,4,7);
				$this->buatCalendar($chatid, $messageid, $month_input, $callback_query_id);
				break;
			default:
				 $this->defaultMessage($chatid, $text, $username);
				 break;
		}//end switch
  }//akhir fungsi respond

	public function webhook()
  {//awal fungsi webhook
		try{//awal try
			$request = Telegram::getWebhookUpdates();

			// if(isset($request['callback_query'])){//buat ngecek apakah yg terbaru itu jenis callback query atau bukan
	    //   $text = $request['callback_query']['data'];
	    //   $chatid = $request['callback_query']['message']['chat']['id'];
			// 	$chatid = $request['callback_query']['message']['chat']['username'];
	    //   $callback_query_id = $request['callback_query']['id'];
	    // }else{//buat kasus $request bukan callback_query
	    //   $text = $request['message']['text'];
	    //   $chatid = $request['message']['chat']['id'];
			// 	$username = $request['message']['chat']['username'];
	    //   $callback_query_id = 0;
	    // }//end else

			if($request->isType('callback_query')){
				$query = $request->getCallbackQuery();
				$text = $query->getData();
				$chatid = $query->getMessage()->getChat()->getId();
				$messageid = $query->getMessage()->getMessageId();
				$username=$query->getMessage()->getChat()->getUsername();
				$callback_query_id = $query->getId();
			}else{
				$chatid = $request->getMessage()->getChat()->getId();
				$text = $request->getMessage()->getText();
				$username=$request->getMessage()->getChat()->getUsername();
				$callback_query_id = 0;
			}//end else

			switch($text)
      {//mulai switch
				case $text === '/start'://udah bisa
					$this->showWelcomeMessage($chatid);
					break;
				case $text==='/menu'://udah bisa
					$this->showMenu($chatid);
					break;
				case $text === 'website'://udah bisa
				   $this->showWebsite($chatid, $callback_query_id);
					   break;
				case $text === 'contact'://udah bisa
				   $this->showContact($chatid, $callback_query_id);
				   break;
				case $text === '/driver'://udah bisa
					$this->showDriverList($chatid, $username, $text);
					break;
				case $text === '/updatedriver'://Udah bisa
					$this->showUpdateDriver($chatid, $username, $text);
					break;
				case $text === '/pesandriver'://udah bisa
					$this->aturPic($chatid);
					break;
				case $text === '/updatetiket'://udah bisa
					$this->updateTiket($chatid, $text, $username);
					break;
				case $text === '/selesai'://BUAT CONFIRM DRIVER SELESAI BERTUGAS
	         $this->konfirmasi($chatid, $username, $text);
	         break;
				//BUAT CONFIRM DRIVER SELESAI BERTUGAS
	      case substr($text,0,8) === '/confirm':
	         $listparams = substr($text,8);
	         $params = explode('#',$listparams);
	         unset($params[0]);
	         $params = array_values($params);

	          if(count($params)==1){
	            $this->updateStatusDriver($chatid, $params);
	          }
	        break;
				//BUAT UPDATE TIKET
				case substr($text,0,7) === '/updtkt':
					$listparams = substr($text,7);
					$params = explode('#',$listparams);
					unset($params[0]);
					$params = array_values($params);

					if(count($params)==1){
						$this->showDataTiket($chatid, $params);
						$this->setDriver($chatid, $params);
					}else{
						$this->updateLog($chatid, $params);
					}
				break;
				//BUAT PESAN DRIVER
				case substr($text,0,7) === '/psndrv':
					$listparams = substr($text,7);
					$params = explode('#',$listparams);
					unset($params[0]);
					$params = array_values($params);

					if(count($params)==1){
						$month_input = date("Y-m");
						$this->tampilCalendar($chatid, $params, $month_input, $callback_query_id);
					}elseif(count($params)==2){
						$this->lokasi($chatid, $params);
					}elseif(count($params)==3){
						$this->simpanPesanan($chatid, $params, $username);
					}
					//$response_txt .= "Mengenal command dan berhasil merespon\n";
					break;
				//BUAT UPDATE DRIVER
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
						$this->saveTheUpdates($chatid, $params, $username);
					}//end elseif

					//$response_txt .= "Mengenal command dan berhasil merespon\n";
					break;

				case substr($text,0,6) === 'change':
					$month_input = substr($text,6,7);
					$this->changeCalendar($chatid, $messageid, $month_input, $callback_query_id);
					break;
				case substr($text,0,4) === 'ubah':
					$month_input = substr($text,4,7);
					$this->buatCalendar($chatid, $messageid, $month_input, $callback_query_id);
					break;
				default:
				   $this->defaultMessage($chatid, $text, $username);
				   break;
			}//end switch
		}catch (\Exception $e) {
			Telegram::sendMessage([
				'chat_id' => $chatid,
				'text' => "Reply ".$e->getMessage()
			]);
		}//end catch
	}//akhir fungsi webhook


	/*
		INI FUNGSI UMUM
	*/
	public function showWelcomeMessage($chatid)
  {//awal fungsi
		$message = "Sugeng rawuh. Bot Kanwil Yogya siap membantu seadanya";
		$response = Telegram::sendMessage([
			'chat_id' => $chatid,
			'parse_mode' => 'markdown',
			'text' => $message
		]);
	}//akhir fungsi

	public function defaultMessage($chatid, $text, $username) //ini untuk menampilkan pesan default
  {
		$message = "Mau apa hayo? Bingung? cek /menu";
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

	public function showMenu($chatid, $info = null)//fungsi buat nampilin menu
  {//awal fungsi
		// this will create keyboard buttons for users to touch instead of typing commands
		$inlineLayout = [[
			Keyboard::inlineButton(['text' => 'Our site', 'callback_data' => 'website']),
			Keyboard::inlineButton(['text' => 'Contact Us', 'callback_data' => 'contact'])
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
    }//end if
		$message = 'Silakan hubungi admin kami di : irfanprabaswara@gmail.com';

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


	/*
		CODE UPDATE DRIVER BRO
		SEMANGAT MENCOBA
	*/
	public function showDriverList($chatid, $username, $text)//fungsi buat nampilin data driver
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

	public function showUpdateDriver($chatid, $username, $text)//fungsi buat update driver
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
					$driverperrow[] = Keyboard::inlineButton(['text' => $result[$i]->nama, 'callback_data' => '/upddrv#'.$result[$i]->id]);
				}else{
					$col=0;
					$driver[] = $driverperrow;
					$driverperrow = [];
					$driverperrow[] = Keyboard::inlineButton(['text' => $result[$i]->nama, 'callback_data' => '/upddrv#'.$result[$i]->id]);
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
  {//awal fungsi pic
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
	}//akhir fungsi pic

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
	}//akhir fungsi change calendar

	public function createCalendar($month_input, $params)//fungsi buat bikin kalender
  {//awal fungsi create calendar
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
	}//akhir fungsi create calendar

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

	public function saveTheUpdates($chatid, $params, $username)
  {//awal fungsi save updates
		$idDriver=$params[0];
		$status="";
		if($params[1]=="set"){
			$status= "Terpakai";
		}
		$result = DB::table('log_driver')->insert(['tanggal'=>date('Y-m-d H:i:s'),'Id'=>$params[0],'pic'=>$params[2],'tanggal_mulai'=>$params[3], 'lokasi'=>$params[4]]);
		$result = DB::table('driver')->where(['Id'=>$params[0]])->update(['status'=>$status]);
		$pesan="Hallo, anda telah dipesan oleh bagian ".$params[2]." dengan tujuan ".$params[4]." pada tanggal ".$params[3].". Silakan hubungi @".$username." untuk waktu keberangkatan";
		$message = "Data Driver berhasil terupdate\n";
		$response = Telegram::sendMessage([
			'chat_id' => $chatid,
			'text' => $message
		]);
		$response = Telegram::sendMessage([
		  'chat_id' => 437329516,//kalo mau ke supirnya tinggal diganti @idDriver
		  'parse_mode' => 'markdown',
		  'text' => $pesan
		]);
	}//akhir fungsi save updates


	/*
		INI BUAT KODE PESAN DRIVER
	*/

	public function lokasi($chatid, $params)//fungsi buat milih tujuan kerja
  {//awal fungsi
		$message="";
		$location = [];
		$locationlist = ['DALAM KOTA', 'LUAR KOTA'];
		$message = "*PILIH LOKASI PENUGASAN* \n\n";
		$max_col = 4;
		$col =0;
		for ($i=0;$i<count($locationlist);$i++){
			if($col<$max_col){
				$locationperrow[] = Keyboard::inlineButton(['text' => $locationlist[$i], 'callback_data' => '/psndrv#'.$params[0]."#".$params[1]."#".$locationlist[$i]]);
			}else{
				$col=0;
				$location[] = $locationperrow;
				$location = [];
				$location[] = Keyboard::inlineButton(['text' => $locationlist[$i], 'callback_data' => '/psndrv#'.$params[0]."#".$params[1]."#".$locationlist[$i]]);
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

	public function tampilCalendar($chatid, $params, $month_input, $cbid)
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
		$calendar = $this->buatCalendar($month_input, $params);

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
	}//akhir fungsi show calendar

	public function ubahCalendar($chatid, $messageid, $month_input, $cbid)
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
		$calendar = $this->buatCalendar($month_input);

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
	}//akhir fungsi change calendar

	public function buatCalendar($month_input, $params)//fungsi buat bikin kalender
  {//awal fungsi create calendar
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
					$calendarperrow[] = Keyboard::inlineButton(['text' => substr("0".strval($date),-2), 'callback_data' => '/psndrv#'.$params[0]."#".$month_input."-".substr("0".strval($date),-2)]);
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
			Keyboard::inlineButton(['text' => 'Previous', 'callback_data' => "ubah".$prev_date]),
			Keyboard::inlineButton(['text' => 'Next', 'callback_data' => "ubah".$next_date])
		];
		$calendar[] = $calendarperrow;

		return $calendar;
	}//akhir fungsi create calendar

	public function aturPic($chatid)
	{//awal fungsi pic
		$message="";
		$pic = [];
		$result=DB::table('driver')->where(['status'=>""])->get();
		$piclist = ['LOG','SDM','MRK','LEGAL','OJL','ECH','KONSUMER','AO','BIT','ARK','ADK','RPKB','EBK','PRG','DJS','BRILINK','RTL','MKR','WPO','WPB1','WPB2','WPB3','WPB4','PINWIL','KANPUS','PIHAK LUAR','LAIN-LAIN'];
		$message = "*PILIH BAGIAN ANDA* \n\n";
		$max_col = 4;
		$col =0;
		if ($result->count()>0) {
			for ($i=0;$i<count($piclist);$i++){
				if($col<$max_col){
					$picperrow[] = Keyboard::inlineButton(['text' => $piclist[$i], 'callback_data' => '/psndrv#'.$piclist[$i]]);
				}else{
					$col=0;
					$pic[] = $picperrow;
					$picperrow = [];
					$picperrow[] = Keyboard::inlineButton(['text' => $piclist[$i], 'callback_data' => '/psndrv#'.$piclist[$i]]);
				}//end else
				$col++;
			}//end for
			if($col>0){
				$col=0;
				$pic[] = $picperrow;
			}//endif

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

		}else {
			$response = Telegram::sendMessage([
				'chat_id' => $chatid,
				'text' => "Driver tidak tersedia"
			]);
		}
	}//ini akhir fungsi pic

	// public function pesanUser($chatid)//ini buat nampilin nomor tiket ke user, tapi masih gagal
	// {//awal fungsi pesan ke user
	// 	$result = DB::table('pemesanan')->where(['pic'=>$params[0],'tanggal'=>$params[1], 'lokasi'=>$params[2]])->first();
	// 	$message = "Pesanan anda dengan nomor tiket : ".$result->nomer_tiket." telah kami terima dan akan kami proses lebih lanjut."
	// 	$response = Telegram::sendMessage([
	// 	  'chat_id' => $chatid,//kalo mau ke supirnya tinggal diganti @idDriver
	// 	  'parse_mode' => 'markdown',
	// 	  'text' => $message
	// 	]);
	// }//akhir fungsi pesan ke user

	public function simpanPesanan($chatid, $params, $username)
  {//awal fungsi
		$status="";
		$result = DB::table('pemesanan')->insert(['pic'=>$params[0],'username'=>$username,'chatid'=>$chatid,'tanggal'=>$params[1], 'lokasi'=>$params[2]]);
		$pesan="Hallo, ada pemesanan dari bagian ".$params[0]." atas nama ".$username." dengan tujuan ".$params[2]." pada tanggal ".$params[1].". Silakan click /updatetiket untuk memproses tiket yang ada";
		$message = "*Pemesanan Berhasil*\n";
		$response = Telegram::sendMessage([
			'chat_id' => $chatid,
			'parse_mode' => 'markdown',
			'text' => $message
		]);
		// $this->pesanUser($chatid);
		$response = Telegram::sendMessage([
		  'chat_id' => 437329516,//kalo mau ke supirnya tinggal diganti @idDriver
		  'parse_mode' => 'markdown',
		  'text' => $pesan
		]);
	}//akhir fungsi



	/*
		BUAT UPDATE TIKET
		KALO ERROR HARAP BERSABAR
		HAHAHAHAA
	*/
	public function updateLog($chatid, $params)
  {//awal fungsi updateLog
		$nomor=$params[0];
    $idDriver=$params[1];
    // $nomor='13';
    // $idDriver='549021135';
		$statusDriver="Terpakai";
    $statusTiket="SELESAI";
    $get = DB::table('pemesanan')->where(['no_tiket'=>$nomor])->first();
    $result = DB::table('driver')->where(['id'=>$idDriver])->first();
    DB::table('log_driver')->insert(['tanggal'=>date('Y-m-d H:i:s'),'id'=>$idDriver,'pic'=>$get->pic,'tanggal_mulai'=>$get->tanggal, 'lokasi'=>$get->lokasi]);
    DB::table('pemesanan')->where(['no_tiket'=>$nomor])->update(['status'=>$statusTiket]);
    DB::table('driver')->where(['id'=>$idDriver])->update(['status'=>$statusDriver]);
		$pesan="Hallo, anda telah dipesan oleh bagian ".$get->pic." atas nama ".$get->username." dengan tanggal keberangkatan ".$get->tanggal." dengan tujuan ".$get->lokasi."";
		$message = "Data Driver berhasil terupdate\n";
    $pesanUser="Pesanan anda dengan tujuan ".$get->lokasi." untuk tanggal keberangkatan ".$get->tanggal." telah diproses dengan nomer tiket ".$nomor.". Silakan berkoordinasi lebih lanjut dengan Bapak ".$result->nama." selaku driver yang akan mengantar anda.";

    $response = Telegram::sendMessage([//buat ngirim ke pemesan
			'chat_id' => $get->chatid,
			'text' => $pesanUser
		]);

		$response = Telegram::sendMessage([//buat ngirim ke admin
			'chat_id' => $chatid,
			'text' => $message
		]);

		$response = Telegram::sendMessage([//buat ngirim ke supir
		  'chat_id' => 437329516,//kalo mau ke supirnya tinggal diganti @idDriver
		  'text' => $pesan
		]);
	}//akhir fungsi updateLog

  // public function statusDriver($params)//kayanya bener
  // {//awal fungsi statusDriver
  //   $nomor=$params[0];
  //   $idDriver=$params[1];
  //   $status ="Terpakai";
  //   $result = DB::table('driver')->where(['id'=>$idDriver])->update(['status'=>$status]);
  // }//akhir fungsi statusDriver
  //
  // public function statusPemesanan($params)//kayanya bener
  // {//awal fungsi statusPemesanan
  //   $nomor=$params[0];
  //   $idDriver=$params[1];
  //   $status ="SELESAI";
  //   $result = DB::table('pemesanan')->where(['no_tiket'=>$nomor])->update(['status'=>$status]);
  // }//akhir fungsi  statusPemesanan

  public function setDriver($chatid, $params)//fungsi buat update driver
  {//awal fungsi
		$driver = [];
		$keyboard = [];
		$message="";
    $nomor=$params[0];
		$result = DB::table('driver')->where(['status'=>""])->get();
		$message = "*PILIH DRIVER YANG AKAN DI-UPDATE* \n\n";
		$max_col = 3;
		$col =0;
		if ($result->count()>0){
			for ($i=0;$i<$result->count();$i++){
				if($col<$max_col){
					$driverperrow[] = Keyboard::inlineButton(['text' => $result[$i]->nama, 'callback_data' => '/updtkt#'.$params[0]."#".$result[$i]->id]);
				}else{
					$col=0;
					$driver[] = $driverperrow;
					$driverperrow = [];
					$driverperrow[] = Keyboard::inlineButton(['text' => $result[$i]->nama, 'callback_data' => '/updtkt#'.$params[0]."#".$result[$i]->id]);
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

  public function updateTiket($chatid, $text, $username)//udah bisa
  {//awal fungsi update tiket
    $result = DB::table('pemesanan')->where(['status'=>null])->get();
    if ($result->count()>0){
    $message = "*PILIH TIKET YANG AKAN DI-UPDATE* \n\n";
		$max_col = 2;
		$col =0;
		if ($result->count()>0){
			for ($i=0;$i<$result->count();$i++){
				if($col<$max_col){
					$tiketperrow[] = Keyboard::inlineButton(['text' => $result[$i]->no_tiket, 'callback_data' => '/updtkt#'.$result[$i]->no_tiket]);
				}else{
					$col=0;
					$tiket[] = $tiketperrow;
					$tiketperrow = [];
					$tiketperrow[] = Keyboard::inlineButton(['text' => $result[$i]->no_tiket, 'callback_data' => '/updtkt#'.$result[$i]->no_tiket]);
				}//end else
				$col++;
			}//end for
		}//end if
		if($col>0){
			$col=0;
			$tiket[] = $tiketperrow;
		}//end if

    $reply_markup = Telegram::replyKeyboardMarkup([
			'resize_keyboard' => true,
			'one_time_keyboard' => true,
		  'inline_keyboard' => $tiket
		]);

		$response = Telegram::sendMessage([
		  'chat_id' => $chatid,
		  'parse_mode' => 'markdown',
		  'text' => $message,
		  'reply_markup' => $reply_markup
		]);

		$response = Telegram::sendMessage([
			'chat_id' => 437329516,
			// 'parse_mode' => 'markdown',
			'text' => "akun : ".$username." telah mengirim pesan ".$text." ke bot anda"
		]);

  }else {
    $message = "*TIKET KOSONG*";
    $response = Telegram::sendMessage([
		  'chat_id' => $chatid,
		  // 'parse_mode' => 'markdown',
		  'text' => $message
		]);
  }//end else
  }//akhir fungsi update tiket

  public function showDataTiket($chatid, $params)//udah bisa tampil
  {//awal fungsi show tiket
    $message="";
    $nomor=$params[0];
		$result = DB::table('pemesanan')->where(['no_tiket'=>$nomor])->first();
		$message = "*DETAIL PESANAN* \n\n";
		$message .= "NOMOR TIKET : ".$result->no_tiket."\n";
    $message .= "NAMA PEMESAN : ".$result->username."\n";
    $message .= "PIC : ".$result->pic."\n";
    $message .= "TANGGAL PENUGASAN : ".$result->tanggal."\n";
    $message .= "TUJUAN PENUGASAN : ".$result->lokasi."\n";
    // $driver[] = Keyboard::inlineButton(['text' => "URUS", 'callback_data' => '/updtkt#'.$params[0]]);

		$response = Telegram::sendMessage([
			'chat_id' => $chatid,
			'parse_mode' => 'markdown',
			'text' => $message
		]);
  }//akhir fungsi show tiket



	/*
		INI KODE KONFIRMASI DRIVER SELESAI BERTUGAS
		SELAMAT MENIKMATI
	*/
	public function konfirmasi($chatid, $username, $text)
  {//awal fungsi konfirmasi
    $get=DB::table('driver')->where(['id'=>$chatid])->first();
    $getStatus=[];
    $getStatus=$get->status;
    $idDriver=[];
    $idDriver=$chatid;
    if ($getStatus==='Terpakai'){
    $pesanDriver="Terima kasih atas konfirmasi dan kerjasama anda.";
    $message="Driver atas nama ".$username." telah selesai mengerjakan tugas. Silakan click disini untuk mengubah status driver yang bersangkutan menjadi stanby";
    $inlineLayout = [[
			Keyboard::inlineButton(['text' => 'DISINI', 'callback_data' => '/confirm#'.$idDriver])
		]];

    $response = Telegram::sendMessage([
			'chat_id' => $chatid,
			'text' => $pesanDriver
		]);

    $reply_markup = Telegram::replyKeyboardMarkup([
			'resize_keyboard' => true,
			'one_time_keyboard' => true,
		  'inline_keyboard' => $inlineLayout
		]);

    $response = Telegram::sendMessage([
		  'chat_id' => 437329516,
		  'parse_mode' => 'markdown',
		  'text' => $message,
		  'reply_markup' => $reply_markup
		]);

    }else {
      $response = Telegram::sendMessage([
  			'chat_id' => $chatid,
  			'text' => "Anda masih dalam status STANDBY".$text
  		]);
    }//akhir else
  }//akhir fungsi konfirmasi

  public function updateStatusDriver($chatid, $params)
  {//awal fungsi updateLog
    $idDriver=$params[0];
		$statusDriver="";
    DB::table('driver')->where(['id'=>$idDriver])->update(['status'=>$statusDriver]);
    $message="Status driver telah terupdate";

    $response = Telegram::sendMessage([//buat ngirim ke pemesan
			'chat_id' => $chatid,
			'text' => $message
		]);
	}//akhir fungsi updateLog

}//akhir kelas
?>
