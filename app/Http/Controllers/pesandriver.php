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
			case $text === '/start':
				$this->defaultMessage($chatid, $text, $username);
				break;
			case $text === '/pesandriver'://udah bisa
				$this->aturPic($chatid);
				break;
			case $text==='tidakJadi':
				$this->tidakJadi($chatid);
				break;

			case substr($text,0,7) === '/psndrv':
				$listparams = substr($text,7);
				$params = explode('#',$listparams);
				unset($params[0]);
				$params = array_values($params);

				if(count($params)==1){
					$this->tampilCalendar($chatid, $params, $month_input, $callback_query_id);
				}elseif(count($params)==2){
					$this->lokasi($chatid, $params);
				}elseif(count($params)==3){
					$this->cekPesan($chatid, $params);
				}elseif (count($params)==4) {
					$this->simpanPesanan($chatid, $params, $username);
				}
				//$response_txt .= "Mengenal command dan berhasil merespon\n";
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
				case $text === '/pesandriver'://udah bisa
					$this->aturPic($chatid);
					break;

				case substr($text,0,7) === '/psndrv':
					$listparams = substr($text,7);
					$params = explode('#',$listparams);
					unset($params[0]);
					$params = array_values($params);

					if(count($params)==1){
						$this->tampilCalendar($chatid, $params, $month_input, $callback_query_id);
					}elseif(count($params)==2){
						$today = strftime('%F');
						if ($params[1]<$today) {
							$this->pesanError($chatid);
						}else {
							$this->lokasi($chatid, $params);
						}
					}elseif(count($params)==3){
						$this->cekPesan($chatid, $params);
					}elseif (count($params)==4) {
						$this->simpanPesanan($chatid, $params, $username);
					}
					//$response_txt .= "Mengenal command dan berhasil merespon\n";
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
				'chat_id' => 437329516,
				'text' => "Reply ".$e->getMessage()
			]);
		}//end catch
	}//akhir fungsi webhook

	public function pesanError($chatid)
	{
		$message="Tanggal penugasan sudah kadaluarsa. \nSilakan pilih kembali tanggal keberangkatan diatas atau klik /pesandriver untuk pemesanan ulang.";
		$response= Telegram::sendMessage([
			'chat_id' => $chatid,
			'text' => $message
		]);
	}

	public function cekPesan($chatid, $params)
	{//awal fungsi
		$setlist=['BENAR','CANCEL'];
		$message = "*DETAIL PESANAN ANDA*\n\n";
		$message .= "BAGIAN ANDA : ".$params[0]."\n";
		$message .= "TANGGAL PENUGASAN : ".$params[1]."\n";
		$message .= "LOKASI PENUGASAN : ".$params[2]."\n\n";
		$message .= "SILAKAN KLIK BENAR UNTUK MELANJUTKAN PEMESANAN DRIVER\n";

		$max_col = 2;
		$col =0;
		for ($i=0;$i<count($setlist);$i++){
			if($col<$max_col){
				$setperrow[] = Keyboard::inlineButton(['text' => $setlist[$i], 'callback_data' => '/psndrv#'.$params[0]."#".$params[1]."#".$params[2]."#".$setlist[$i]]);
			}else{
				$col=0;
				$set[] = $setperrow;
				$set = [];
				$set[] = Keyboard::inlineButton(['text' => $setlist[$i], 'callback_data' => '/psndrv#'.$params[0]."#".$params[1]."#".$params[2]."#".$setlist[$i]]);
			}//end else
			$col++;
		}//end for
		if($col>0){
			$col=0;
			$set[] = $setperrow;
		}//end if

		// create an instance of the replyKeyboardMarkup method
		$keyboard = Telegram::replyKeyboardMarkup([
			'resize_keyboard' => true,
			'one_time_keyboard' => true,
			'inline_keyboard' => $set
		]);

		// Now send the message with they keyboard using 'reply_markup' parameter
		$response = Telegram::sendMessage([
			'chat_id' => $chatid,
			'text' => $message,
			'parse_mode' => 'markdown',
			'reply_markup' => $keyboard
		]);
	}//akhir fungsi

	public function lokasi($chatid, $params)//fungsi buat milih tujuan kerja
  {//awal fungsi
		$message="";
		$location = [];
		$locationlist = ['DALAM KOTA', 'LUAR KOTA'];
		$message = "*PILIH LOKASI PENUGASAN* \n\n";
		$max_col = 2;
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

	public function ubahCalendar($chatid, $messageid, $month_input)
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
		$result=DB::table('driver')->where(['status'=>"Standby"])->get();
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
		if($params[3]==="BENAR"){
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
	}else {
		$message = "Silakan klik /pesandriver untuk melakukan pemesanan ulang";
		$response=Telegram::sendMessage([
			'chat_id'=>$chatid,
			'text'=>$message
		]);
	}//akhir else
	}//akhir fungsi

}//akhir kelas

?>
