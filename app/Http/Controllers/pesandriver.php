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
use App\Pemesanan;
use App\Tiket;

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
					$month_input = date("Y-m");
					$this->tampilCalendar($chatid, $month_input);
					break;

				case substr($text,0,7) === '/psndrv':
					$listparams = substr($text,7);
					$params = explode('#',$listparams);
					unset($params[0]);
					$params = array_values($params);

					if(count($params)==1){
						$apaya=substr($params[0],0,4);
						if ($apaya === 'ubah') {
							$month_input = substr($params[0],4,7);
							$this->ubahCalendar($chatid, $messageid, $month_input, $params);
						}//endif
						else {
							$today = strftime('%F');
							if ($params[0]<$today) {
								$this->pesanError($chatid);
							}else {
							$tanggals = $params[0];

								$cekDriver=DB::table('driver')
										->leftjoin('tiket', function($join){
																	$join->on('driver.id','=','id_driver')
																			 ->on('tanggal','=',DB::raw( '?'));
										})
										->where(function ($query){
													$query  ->whereNull('id_driver')
																	->orWhere('status','=',DB::raw( '?'));
												})
										->setBindings([$tanggals,'SELESAI'])
										->get();
									if ($cekDriver->count()>0) {
										$this->aturPic($chatid, $params);
									}
									else {
										$message="*MAAF, DRIVER PENUH*";
										$response=Telegram::sendMessage([
											'chat_id'=>$chatid,
											'text'=>$message,
											'parse_mode'=>'markdown'
										]);
									}//end else
							}//end else
					}//end else
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
						$month_input = date("Y-m");
						$this->tampilCalendar($chatid, $month_input);
						break;

					case substr($text,0,7) === '/psndrv':
						$listparams = substr($text,7);
						$params = explode('#',$listparams);
						unset($params[0]);
						$params = array_values($params);

						if(count($params)==1){
							$apaya=substr($params[0],0,4);
							if ($apaya === 'ubah') {
								$month_input = substr($params[0],4,7);
								$this->ubahCalendar($chatid, $messageid, $month_input, $params);
							}//endif
							else {
								$today = strftime('%F');
								if ($params[0]<$today) {
									$this->pesanError($chatid, $messageid);
								}else {
								$tanggals = $params[0];

									$cekDriver=DB::table('driver')
											->leftjoin('tiket', function($join){
																		$join->on('driver.id','=','id_driver')
																				 ->on('tanggal','=',DB::raw( '?'));
											})
											->where(function ($query){
								  					$query  ->whereNull('id_driver')
								  									->orWhere('status','=',DB::raw( '?'));
								  				})
											->setBindings([$tanggals,'SELESAI'])
								  		->get();
						  			if ($cekDriver->count()>0) {
											$this->aturPic($chatid, $params, $messageid);
										}
										else {
											$message="*MAAF, DRIVER PENUH*";
											$response=Telegram::sendMessage([
												'chat_id'=>$chatid,
												'text'=>$message,
												'parse_mode'=>'markdown'
											]);
										}//end else
								}//end else
						}//end else
						}elseif(count($params)==2){
							$this->lokasi($chatid, $params, $messageid);
						}elseif(count($params)==3){
							$this->cekPesan($chatid, $params, $messageid);
						}elseif (count($params)==4) {
							$this->simpanPesanan($chatid, $params, $username, $messageid);
						}
						//$response_txt .= "Mengenal command dan berhasil merespon\n";
						break;
						case substr($text,0,4) === 'ubah':
							$month_input = substr($text,4,7);
							$this->buatCalendar($chatid, $messageid, $month_input, $callback_query_id);
							break;

				default:
					 $this->defaultMessage($chatid, $text, $username, $messageid);
					 break;
			}//end switch
		}catch (\Exception $e) {
			Telegram::sendMessage([
				'chat_id' => 437329516,
				'text' => "Reply ".$e->getMessage()
			]);
		}//end catch
	}//akhir fungsi webhook

	public function pesanError($chatid, $messageid)
	{

		$message="Tanggal penugasan sudah kadaluarsa. \nSilakan pilih kembali tanggal keberangkatan diatas atau klik /pesandriver untuk pemesanan ulang.";
		$response = Telegram::editMessageText([
			'chat_id' => $chatid,
			'parse_mode' => 'markdown',
			'message_id' =>$messageid,
			'text' => $message
		]);
	}

	public function cekPesan($chatid, $params, $messageid)
	{//awal fungsi
		$setlist=['BENAR','CANCEL'];
		$message = "*DETAIL PESANAN ANDA*\n\n";
		$message .= "BAGIAN ANDA : ".$params[1]."\n";
		$message .= "TANGGAL PENUGASAN : ".$params[0]."\n";
		$message .= "LOKASI PENUGASAN : ".$params[2]."\n\n";
		$message .= "SILAKAN KLIK BENAR UNTUK MELANJUTKAN PEMESANAN DRIVER\n";
		$setperrow = [];
		$max_col = 2;
		$col =0;

		for ($i=0;$i<count($setlist);$i++){
			if($col<$max_col){
				$setperrow[] = Keyboard::inlineButton(['text' => $setlist[$i], 'callback_data' => '/psndrv#'.$params[0]."#".$params[1]."#".$params[2]."#".$setlist[$i]]);
			}else{
				$col=0;
				$set[] = $setperrow;
				$setperrow = [];
				$setperrow[] = Keyboard::inlineButton(['text' => $setlist[$i], 'callback_data' => '/psndrv#'.$params[0]."#".$params[1]."#".$params[2]."#".$setlist[$i]]);
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
		$response = Telegram::editMessageText([
			'chat_id' => $chatid,
			'parse_mode' => 'markdown',
			'message_id' =>$messageid,
			'text' => $message,
			'reply_markup' => $keyboard
		]);
	}//akhir fungsi

	public function lokasi($chatid, $params, $messageid)//fungsi buat milih tujuan kerja
  {//awal fungsi
		$message="";
		$location = [];
		$locationperrow = [];
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
				$locationperrow = [];
				$locationperrow[] = Keyboard::inlineButton(['text' => $locationlist[$i], 'callback_data' => '/psndrv#'.$params[0]."#".$params[1]."#".$locationlist[$i]]);
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

		$response = Telegram::editMessageText([
			'chat_id' => $chatid,
			'parse_mode' => 'markdown',
			'message_id' =>$messageid,
			'text' => $message,
			'reply_markup' => $reply_markup
		]);
	}//akhir fungsi

	public function tampilCalendar($chatid, $month_input)//udah bener
  {//awal fungsi

		$message = "*PILIH TANGGAL PENUGASAN*\n";
		$message .= DateTime::createFromFormat('Y-m-d',$month_input."-01")->format("F Y")." \n";
		$calendar = $this->buatCalendar($month_input);

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

	public function ubahCalendar($chatid, $messageid, $month_input)//masih error
  {//awal fungsi
		$message = "*PILIH TANGGAL PENUGASAN*\n";
		$message .= DateTime::createFromFormat('Y-m-d',$month_input."-01")->format("F Y")." \n";

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
					$calendarperrow[] = Keyboard::inlineButton(['text' => substr("0".strval($date),-2), 'callback_data' => '/psndrv#'.$month_input."-".substr("0".strval($date),-2)]);
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
			Keyboard::inlineButton(['text' => 'Previous', 'callback_data' => '/psndrv#ubah'.$prev_date]),
			Keyboard::inlineButton(['text' => 'Next', 'callback_data' => '/psndrv#ubah'.$next_date])
		];
		$calendar[] = $calendarperrow;

		$reply_markup = Telegram::replyKeyboardMarkup([
			'resize_keyboard' => true,
			'one_time_keyboard' => true,
		  'inline_keyboard' => $calendar
		]);

		$response = Telegram::editMessageText([
		  'chat_id' => $chatid,
		  'parse_mode' => 'markdown',
		  'message_id' =>$messageid,
		  'text' => $message,
		  'reply_markup' => $reply_markup
		]);
	}//akhir fungsi ubah calendar

  public function buatCalendar($month_input)//fungsi buat bikin kalender
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
					$calendarperrow[] = Keyboard::inlineButton(['text' => substr("0".strval($date),-2), 'callback_data' => '/psndrv#'.$month_input."-".substr("0".strval($date),-2)]);
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
			Keyboard::inlineButton(['text' => 'Previous', 'callback_data' => '/psndrvv#ubah'.$prev_date]),
			Keyboard::inlineButton(['text' => 'Next', 'callback_data' => '/psndrv#ubah'.$next_date])
		];
		$calendar[] = $calendarperrow;

		return $calendar;
	}//akhir fungsi create calendar

	public function aturPic($chatid, $params, $messageid)
	{//awal fungsi pic
		$message="";
		$pic = [];
		$picperrow = [];
		$tanggals = $params[0];
		$piclist = ['LOG','SDM','MRK','LEGAL','OJL','ECH','KONSUMER','AO','BIT','ARK','ADK','RPKB','EBK','PRG','DJS','BRILINK','RTL','MKR','WPO','WPB1','WPB2','WPB3','WPB4','PINWIL','KANPUS','PIHAK LUAR','LAIN-LAIN'];
		$message = "*PILIH PIC YANG PESAN* \n\n";
		$max_col = 4;
		$col =0;
		// if ($result->count()<2) {
			for ($i=0;$i<count($piclist);$i++){
				if($col<$max_col){
					$picperrow[] = Keyboard::inlineButton(['text' => $piclist[$i], 'callback_data' => '/psndrv#'.$params[0]."#".$piclist[$i]]);
				}else{
					$col=0;
					$pic[] = $picperrow;
					$picperrow = [];
					$picperrow[] = Keyboard::inlineButton(['text' => $piclist[$i], 'callback_data' => '/psndrv#'.$params[0]."#".$piclist[$i]]);
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

			$response = Telegram::editMessageText([
			  'chat_id' => $chatid,
			  'parse_mode' => 'markdown',
			  'message_id' =>$messageid,
			  'text' => $message,
			  'reply_markup' => $reply_markup
			]);

	}//ini akhir fungsi pic

	public function simpanPesanan($chatid, $params, $username, $messageid)
  {//awal fungsi
		$status="";
		$syarat=$params[3];
		if($params[3]=='BENAR'){
				$status="";
				$newpemesanan=Tiket::insertGetId([
					'chatid'=>$chatid,
					'username'=>$username,
					'pic'=>$params[1],
					'tanggal'=>$params[0],
					'lokasi'=>$params[2]
				]);
				// DB::table('tiket')->insertGetId(array('chatid'=>$chatid, 'username'=>$username, 'pic'=>$params[1], 'tanggal'=>$params[0], 'lokasi'=>$params[2]));
				//DB::table('pemesanan')->insert(['pic'=>$params[1],'username'=>$username,'chatid'=>$chatid,'tanggal'=>$params[0], 'lokasi'=>$params[2]]);
				$pesan="Hallo, ada pemesanan dari bagian ".$params[1]." atas nama ".$username." dengan tujuan ".$params[2]." pada tanggal ".$params[0].". Silakan click /updatetiket untuk memproses tiket yang ada";
				$message = "*Pemesanan Berhasil. Nomor tiket anda adalah : $newpemesanan*\n";
				//$result=

				$response = Telegram::editMessageText([
				  'chat_id' => $chatid,
				  'parse_mode' => 'markdown',
				  'message_id' =>$messageid,
				  'text' => $message
				]);

				// $this->pesanUser($chatid);
				$response = Telegram::sendMessage([
				  'chat_id' => 437329516,//kalo mau ke admin tinggal diganti @idAdmin
				  'parse_mode' => 'markdown',
				  'text' => $pesan
				]);
		}else {
			$message = "Silakan klik /pesandriver untuk melakukan pemesanan ulang";
			$response = Telegram::editMessageText([
			  'chat_id' => $chatid,
			  'parse_mode' => 'markdown',
			  'message_id' =>$messageid,
			  'text' => $message,
			]);
		}//akhir else
	}//akhir fungsi

}//akhir kelas

?>
