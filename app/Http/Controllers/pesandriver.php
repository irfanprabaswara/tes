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

class pesandriver extends Controller
{//awal kelas
	public function webhook()
	{
	try{//awal try
		$request = Telegram::getWebhookUpdates();
		$month_input = date("Y-m");
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
				case $text === '/start':
					$this->defaultMessage($chatid, $text, $username);
					break;
				case $text === '/pesan'://udah bisa
					$this->setPic($chatid);
					break;

				case substr($text,0,7) === '/psndrv':
					$listparams = substr($text,7);
					$params = explode('#',$listparams);
					unset($params[0]);
					$params = array_values($params);

					if(count($params)==1){
						$this->showCalendar($chatid, $params, $month_input, $callback_query_id);
					}elseif(count($params)==2){
						$this->setLocation($chatid, $params);
					}elseif(count($params)==3){
						$this->saveTheUpdates($chatid, $params, $username);
					}
					//$response_txt .= "Mengenal command dan berhasil merespon\n";
					break;

				case substr($text,0,6) === 'change':
					$month_input = substr($text,6,7);
					$this->changeCalendar($chatid, $messageid, $month_input, $callback_query_id);
					break;

				default:
					 $this->defaultMessage($chatid, $text, $username);
					 break;
			}//end switch
		}catch (\Exception $e) {
			Telegram::sendMessage([
				'chat_id' => 437329516,
				'text' => "Reply ".$e->getMessage()
			]);
		}//end catch
	}//akhir fungsi webhook

	public function setLocation($chatid, $params)//fungsi buat milih tujuan kerja
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
	}//akhir fungsi show calendar

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
			Keyboard::inlineButton(['text' => 'Previous', 'callback_data' => "change".$prev_date]),
			Keyboard::inlineButton(['text' => 'Next', 'callback_data' => "change".$next_date])
		];
		$calendar[] = $calendarperrow;

		return $calendar;
	}//akhir fungsi create calendar

	public function setPic($chatid)
	{//awal fungsi pic
		$message="";
		$pic = [];
		$result=DB::table('driver')->where(['status'=>""])->get();
		$piclist = ['LOG','SDM','MRK','LEGAL','OJL','ECH','KONSUMER','AO','BIT','ARK','ADK','RPKB','EBK','PRG','DJS','BRILINK','RTL','MKR','WPO','WPB1','WPB2','WPB3','WPB4','PINWIL','KANPUS','PIHAK LUAR','LAIN-LAIN'];
		$message = "*PILIH PIC YANG PESAN* \n\n";
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

	public function saveTheUpdates($chatid, $params, $username)
  {//awal fungsi
		// $idDriver=$params[0];
		$status="";
		if($params[1]=="set"){
			$status= "Terpakai";
		}
		$result = DB::table('pemesanan')->insert(['pic'=>$params[0],'tanggal'=>$params[1], 'tujuan'=>$params[2]]);
		$pesan="Hallo, ada pemesanan dari bagian ".$params[0]." dengan tujuan ".$params[2]." pada tanggal ".$params[1].". Silakan click /updatetiket untuk memproses tiket yang ada";
		$message = "Pemesanan Berhasil\n";
		$response = Telegram::sendMessage([
			'chat_id' => $chatid,
			'text' => $message
		]);
		$response = Telegram::sendMessage([
		  'chat_id' => 437329516,//kalo mau ke supirnya tinggal diganti @idDriver
		  'parse_mode' => 'markdown',
		  'text' => $pesan
		]);
	}//akhir fungsi

}//akhir kelas

?>
