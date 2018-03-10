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

class updatedriver extends Controller
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

        /*BUAT UPDATE DRIVER*/
				case $text === '/updatedriver'://Udah bisa
					$this->menuUpdateDriver($chatid);
					break;
				case $text === 'setPenugasan'://udah bisa
					$month_input = date("Y-m");
					$this->showCalendar($chatid, $month_input);
					break;
				case $text === 'setSelesaiBertugas'://udah bisa
					 $this->setSelesaiBertugas($chatid, $callback_query_id);
					 break;

				case substr($text,0,7) === '/updpsn':
				$listparams = substr($text,7);
				$params = explode('#',$listparams);
				unset($params[0]);
				$params = array_values($params);

				if(count($params)==1){
					$apaya=substr($params[0],0,6);
					if ($apaya === 'change') {
						$month_input = substr($params[0],6,7);
						$this->changeCalendar($chatid, $messageid, $month_input, $params);
					}//endif
					else {
						$today = strftime('%F');
						if ($params[0]<$today) {
							$this->errorMessage($chatid);
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
									$this->setPic($chatid, $params);
								}
								else {
									$message="*DRIVER PENUH UNTUK TANGGAL PENUGASAN TERSEBUT*";
									$response=Telegram::sendMessage([
										'chat_id'=>$chatid,
										'text'=>$message,
										'parse_mode'=>'markdown'
									]);
								}//end else
						}//end else
				}//end else
				}elseif(count($params)==2){
					$this->tujuan($chatid, $params);
				}elseif(count($params)==3){
					$this->cekPenugasan($chatid, $params);
				}elseif (count($params)==4) {
					if ($params[3]==='BENAR') {
						$this->pilihDriver($chatid,$params);
					}
					else {
						$this->menuUpdateDriver($chatid);
					}
				}elseif (count($params)==5) {
					$this->savePenugasan($chatid, $params, $username);
				}
				break;

				case substr($text,0,7) === '/updsts':
						$listparams = substr($text,7);
						$params = explode('#',$listparams);
						unset($params[0]);
						$params = array_values($params);

				if(count($params)==1){
					$get=DB::table('tiket')->where(['id'=>$params[0]])->first();
					$status=$get->status;
					if ($status===null) {
					$this->tampilDataTiket($chatid, $params);
					}else {
						$response=Telegram::sendMessage([
							'chat_id'=>$chatid,
							'text'=>"Tiket telah terupdate"
						]);
					}//end else
				}elseif(count($params)==2){
						$this->updateStatusDriver($chatid, $params);
				}//end elseif
				break;


				case substr($text,0,6) === 'change':
					$month_input = substr($text,6,7);
					$this->buatCalendar($chatid, $messageid, $month_input, $callback_query_id);
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

          /*BUAT UPDATE DRIVER*/
  			case $text === '/updatedriver'://Udah bisa
          $otong=437329516;
          if ($chatid==$otong) {
    				$this->menuUpdateDriver($chatid);
          }
  				break;
        case $text === 'setPenugasan'://udah bisa
          $month_input = date("Y-m");
          $this->showCalendar($chatid, $month_input);
    			break;
    		case $text === 'setSelesaiBertugas'://udah bisa
    			 $this->setSelesaiBertugas($chatid, $callback_query_id);
    			 break;

  			case substr($text,0,7) === '/updpsn':
        $listparams = substr($text,7);
        $params = explode('#',$listparams);
        unset($params[0]);
        $params = array_values($params);

        if(count($params)==1){
          $apaya=substr($params[0],0,6);
          if ($apaya === 'change') {
            $month_input = substr($params[0],6,7);
            $this->changeCalendar($chatid, $messageid, $month_input, $params);
          }//endif
          else {
            $today = strftime('%F');
            if ($params[0]<$today) {
              $this->errorMessage($chatid);
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
                  $this->setPic($chatid, $params);
                }
                else {
                  $message="*DRIVER PENUH UNTUK TANGGAL PENUGASAN TERSEBUT*";
                  $response=Telegram::sendMessage([
                    'chat_id'=>$chatid,
                    'text'=>$message,
                    'parse_mode'=>'markdown'
                  ]);
                }//end else
            }//end else
        }//end else
        }elseif(count($params)==2){
          $this->tujuan($chatid, $params);
        }elseif(count($params)==3){
          $this->cekPenugasan($chatid, $params);
        }elseif (count($params)==4) {
          if ($params[3]==='BENAR') {
            $this->pilihDriver($chatid,$params);
          }
          else {
            $this->menuUpdateDriver($chatid);
          }
        }elseif (count($params)==5) {
          $this->savePenugasan($chatid, $params, $username);
        }
        break;

        case substr($text,0,7) === '/updsts':
            $listparams = substr($text,7);
            $params = explode('#',$listparams);
            unset($params[0]);
            $params = array_values($params);

        if(count($params)==1){
					$get=DB::table('tiket')->where(['id'=>$params[0]])->first();
					$status=$get->status;
					if ($status===null) {
          $this->tampilDataTiket($chatid, $params);
					}else {
						$response=Telegram::sendMessage([
							'chat_id'=>$chatid,
							'text'=>"Tiket telah terupdate"
						]);
					}//end else
        }elseif(count($params)==2){
						$this->updateStatusDriver($chatid, $params);
        }//end elseif
        break;


        case substr($text,0,6) === 'change':
          $month_input = substr($text,6,7);
          $this->buatCalendar($chatid, $messageid, $month_input, $callback_query_id);
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
		Berikut merupakan program untuk update driver
	*/

  public function pilihDriver($chatid, $params)//fungsi buat update driver
  {//awal fungsi
		$driverperrow = [];
    $nomor=$params[0];
    // $get=DB::table('tiket')->where(['id'=>$params[0]])->first();
      $driver = [];
  		$keyboard = [];
  		$message="";

      $tanggals=$params[0];
      $result=DB::table('driver')
           ->select(DB::raw('driver.id as id,nama,tanggal,status'))
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
      // $get = DB::table('tiket')->where(['id'=>$nomor])->first();
      // $tanggal=$get->tanggal;
  		$message = "*PILIH DRIVER YANG AKAN DI-UPDATE* \n\n";
  		$max_col = 3;
  		$col =0;
  		if ($result->count()>0){
  			for ($i=0;$i<$result->count();$i++){
  				if($col<$max_col){
  					$driverperrow[] = Keyboard::inlineButton(['text' => $result[$i]->nama, 'callback_data' => '/updpsn#'.$params[0]."#".$params[1]."#".$params[2]."#".$params[3]."#".$result[$i]->id]);
  				}else{
  					$col=0;
  					$driver[] = $driverperrow;
  					$driverperrow = [];
  					$driverperrow[] = Keyboard::inlineButton(['text' => $result[$i]->nama, 'callback_data' => '/updpsn#'.$params[0]."#".$params[1]."#".$params[2]."#".$params[3]."#".$result[$i]->id]);
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
	}//akhir fungsi pilih driver

  public function menuUpdateDriver($chatid)//fungsi buat nampilin menu
  {//awal fungsi
		// this will create keyboard buttons for users to touch instead of typing commands
    $message="*PILIH TINDAKAN :*";
		$inlineLayout = [[
			Keyboard::inlineButton(['text'=>'SET PENUGASAN', 'callback_data' => 'setPenugasan']),
			Keyboard::inlineButton(['text'=>'SET SELESAI TUGAS', 'callback_data' => 'setSelesaiBertugas'])
		]];

		// create an instance of the replyKeyboardMarkup method
		$keyboard = Telegram::replyKeyboardMarkup([
      'inline_keyboard' => $inlineLayout
		]);

		// Now send the message with they keyboard using 'reply_markup' parameter
		$response = Telegram::sendMessage([
			'chat_id' => $chatid,
			'text' => $message,
      'parse_mode'=>'markdown',
			'reply_markup' => $keyboard
		]);
	}//akhir fungsi

  public function errorMessage($chatid)
	{
		$message="Tanggal penugasan sudah kadaluarsa.";
		$response= Telegram::sendMessage([
			'chat_id' => $chatid,
			'text' => $message
		]);
	}

	public function cekPenugasan($chatid, $params)
	{//awal fungsi
		$setlist=['BENAR','CANCEL'];
		$message = "*DETAIL PENUGASAN*\n\n";
		$message .= "BAGIAN YANG MEMESAN : ".$params[1]."\n";
		$message .= "TANGGAL PENUGASAN : ".$params[0]."\n";
		$message .= "TUJUAN PENUGASAN : ".$params[2]."\n\n";
		$message .= "SILAKAN KLIK BENAR UNTUK MELANJUTKAN PENUGASAN DRIVER\n";
		$setperrow = [];
		$max_col = 2;
		$col =0;

		for ($i=0;$i<count($setlist);$i++){
			if($col<$max_col){
				$setperrow[] = Keyboard::inlineButton(['text' => $setlist[$i], 'callback_data' => '/updpsn#'.$params[0]."#".$params[1]."#".$params[2]."#".$setlist[$i]]);
			}else{
				$col=0;
				$set[] = $setperrow;
				$setperrow = [];
				$setperrow[] = Keyboard::inlineButton(['text' => $setlist[$i], 'callback_data' => '/updpsn#'.$params[0]."#".$params[1]."#".$params[2]."#".$setlist[$i]]);
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

	public function tujuan($chatid, $params)//fungsi buat milih tujuan kerja
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
				$locationperrow[] = Keyboard::inlineButton(['text' => $locationlist[$i], 'callback_data' => '/updpsn#'.$params[0]."#".$params[1]."#".$locationlist[$i]]);
			}else{
				$col=0;
				$location[] = $locationperrow;
				$locationperrow = [];
				$locationperrow[] = Keyboard::inlineButton(['text' => $locationlist[$i], 'callback_data' => '/updpsn#'.$params[0]."#".$params[1]."#".$locationlist[$i]]);
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

	public function showCalendar($chatid, $month_input)//udah bener
  {//awal fungsi

		$message = "*PILIH TANGGAL PENUGASAN*\n";
		$message .= DateTime::createFromFormat('Y-m-d',$month_input."-01")->format("F Y")." \n";
		$calendar = $this->createCalendar($month_input);

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

	public function changeCalendar($chatid, $messageid, $month_input)//masih error
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
					$calendarperrow[] = Keyboard::inlineButton(['text' => substr("0".strval($date),-2), 'callback_data' => '/updpsn#'.$month_input."-".substr("0".strval($date),-2)]);
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
			Keyboard::inlineButton(['text' => 'Previous', 'callback_data' => '/updpsn#change'.$prev_date]),
			Keyboard::inlineButton(['text' => 'Next', 'callback_data' => '/updpsn#change'.$next_date])
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

  public function createCalendar($month_input)//fungsi buat bikin kalender
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
					$calendarperrow[] = Keyboard::inlineButton(['text' => substr("0".strval($date),-2), 'callback_data' => '/updpsn#'.$month_input."-".substr("0".strval($date),-2)]);
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
			Keyboard::inlineButton(['text' => 'Previous', 'callback_data' => '/updpsn#change'.$prev_date]),
			Keyboard::inlineButton(['text' => 'Next', 'callback_data' => '/updpsn#change'.$next_date])
		];
		$calendar[] = $calendarperrow;

		return $calendar;
	}//akhir fungsi create calendar

	public function setPic($chatid, $params)
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
					$picperrow[] = Keyboard::inlineButton(['text' => $piclist[$i], 'callback_data' => '/updpsn#'.$params[0]."#".$piclist[$i]]);
				}else{
					$col=0;
					$pic[] = $picperrow;
					$picperrow = [];
					$picperrow[] = Keyboard::inlineButton(['text' => $piclist[$i], 'callback_data' => '/updpsn#'.$params[0]."#".$piclist[$i]]);
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

	}//ini akhir fungsi pic

	public function savePenugasan($chatid, $params, $username)
  {//awal fungsi
		$status="";
		$idDriver=$params[4];

		if($params[3]=='BENAR'){
				$status="";
				$newpenugasan= DB::table('tiket')->insertGetId(array('chatid'=>$chatid, 'id_driver'=>$idDriver, 'username'=>$username, 'pic'=>$params[1], 'tanggal'=>$params[0], 'lokasi'=>$params[2]));
        $get=DB::table('tiket')->where('id',"=",$newpenugasan)->first();
        $pesan="Hallo, anda telah dipesan oleh bagian ".$get->pic." atas nama ".$get->username." dengan tanggal keberangkatan ".$get->tanggal." dengan tujuan ".$get->lokasi."";
				//DB::table('pemesanan')->insert(['pic'=>$params[1],'username'=>$username,'chatid'=>$chatid,'tanggal'=>$params[0], 'lokasi'=>$params[2]]);
				$message = "*Penugasan Berhasil. Nomor tiket penugasan ini adalah : $newpenugasan*\n";

				$response = Telegram::sendMessage([
					'chat_id' => $chatid,
					'parse_mode' => 'markdown',
					'text' => $message
				]);

				// $this->pesanUser($chatid);
				$response = Telegram::sendMessage([
				  'chat_id' => $idDriver,//kalo mau ke admin tinggal diganti @idAdmin
				  'parse_mode' => 'markdown',
				  'text' => $pesan
				]);
		}else {
			$message = "Silakan klik /updatedriver untuk melakukan penugasan ulang";
			$response=Telegram::sendMessage([
				'chat_id'=>$chatid,
				'text'=>$message
			]);
		}//akhir else
	}//akhir fungsi




	public function setSelesaiBertugas($chatid, $cbid)//fungsi buat nampilin contact
  {//awal fungsi
    $today=date('Y-m-d');
    $result=DB::table('tiket')->where('id_driver',"!=",null)
                              ->where('status',"=",null)
                              ->get();

      if ($result->count()>0){
          $message = "*PILIH TIKET YANG AKAN ANDA SET SELESAI* \n\n";
          $max_col = 1;
          $col =0;
          if ($result->count()>0){
            for ($i=0;$i<$result->count();$i++){
              if($col<$max_col){
                $tiketperrow[] = Keyboard::inlineButton(['text' =>"NOMOR TIKET : ".$result[$i]->id."(".$result[$i]->pic.") (".$result[$i]->tanggal.")", 'callback_data' => '/updsts#'.$result[$i]->id]);
              }else{
                $col=0;
                $tiket[] = $tiketperrow;
                $tiketperrow = [];
                $tiketperrow[] = Keyboard::inlineButton(['text' =>"NOMOR TIKET : ".$result[$i]->id."(".$result[$i]->pic.") (".$result[$i]->tanggal.")", 'callback_data' => '/updsts#'.$result[$i]->id]);
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
    }//end if
    else {
      $message="*TIDAK ADA TIKET AKTIF*";
      $response=Telegram::sendMessage([
        'chat_id'=>$chatid,
        'parse_mode'=>'markdown',
        'text'=>$message
      ]);
    }//akhir else
  }//akhir fungsi

  public function tampilDataTiket($chatid, $params)//udah bisa tampil
  {//awal fungsi show tiket
    $message="";
    $nomor=$params[0];
		$result = DB::table('tiket')->where(['id'=>$nomor])->first();
		$message = "*DETAIL PESANAN* \n\n";
		$message .= "NOMOR TIKET : ".$result->id."\n";
    $message .= "NAMA PEMESAN : ".$result->username."\n";
    $message .= "PIC : ".$result->pic."\n";
    $message .= "TANGGAL PENUGASAN : ".$result->tanggal."\n";
    $message .= "TUJUAN PENUGASAN : ".$result->lokasi."\n";
    // $driver[] = Keyboard::inlineButton(['text' => "URUS", 'callback_data' => '/updtkt#'.$params[0]]);

    $inlineLayout = [[
      Keyboard::inlineButton(['text' => 'SELESAI TUGAS', 'callback_data' => '/updsts#'.$params[0]."#SELESAI"]),
      Keyboard::inlineButton(['text' => 'BATAL', 'callback_data' => 'setSelesaiBertugas'])
    ]];

    $reply_markup = Telegram::replyKeyboardMarkup([
      'resize_keyboard' => true,
      'one_time_keyboard' => true,
      'inline_keyboard' => $inlineLayout
    ]);

    $response = Telegram::sendMessage([
      'chat_id' => $chatid,
      'parse_mode' => 'markdown',
      'text' => $message,
      'reply_markup' => $reply_markup
    ]);
  }//akhir fungsi show tiket

  public function updateStatusDriver($chatid, $params)
  {
    $nomor=$params[0];
		$statusTiket="SELESAI";
    DB::table('tiket')->where(['id'=>$nomor])->update(['status'=>$statusTiket]);
    $message="*STATUS TIKET TELAH DISET SELESAI*";

    $response = Telegram::sendMessage([//buat ngirim ke pemesan
			'chat_id' => $chatid,
      'parse_mode'=>'markdown',
			'text' => $message
		]);
  }//akhir fungsi updateStatusDriver


}//akhir kelas
?>
