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
use App\Tiket;
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
				// case $text === '/driver'://udah bisa
				// 	$this->showDriverList($chatid, $username, $text);
				// 	break;




				/*
				BUAT CONFIRM DRIVER SELESAI BERTUGAS
		// 		*/
		case $text === '/selesai'://udah bisa
			$this->tampilTiketDriver($chatid);
			break;

		case substr($text,0,8) === '/confirm':
			$listparams = substr($text,8);
			$params = explode('#',$listparams);
			unset($params[0]);
			$params = array_values($params);

			if(count($params)==1){
				$this->detailTiket($chatid, $params);
			}
			elseif (count($params)==2) {
				$this->konfirmasi($chatid, $params);
			}
			elseif (count($params)==3) {
				$this->updateStatusTiket($chatid, $params);
			}
		break;

				/*
				BUAT UPDATE TIKET
				*/
				case $text === '/updatetiket'://udah bisa
          $this->updateTiket($chatid, $text, $username);
          break;

        case substr($text,0,7) === '/updtkt':
          $listparams = substr($text,7);
          $params = explode('#',$listparams);
          unset($params[0]);
          $params = array_values($params);

          if(count($params)==1){
            $this->showDataTiket($chatid, $params);
          }elseif (count($params)==2) {
            $nomor = $params[0];
            $get=DB::table('tiket')->where(['id'=>$nomor])->first();
            $tanggals=$get->tanggal;
            if ($params[1]==="APPROVE") {
              $result=DB::table('driver')
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
              if ($result->count()>0){
                $nomor = $params[0];
                $get=DB::table('tiket')->where(['id'=>$nomor])->first();
                if ($get->id_driver==null) {
                  $this->setDriver($chatid, $params);
                }else {
                  $message = "Tiket sudah terupdate";
                  $response=Telegram::sendMessage([
                    'chat_id'=>$chatid,
                    'text'=>$message
                  ]);
                }//end else
              }else {
                $this->pesanDriverHabis($chatid);
              }
            }else {
              $nomor = $params[0];
              $get=DB::table('tiket')->where(['id'=>$nomor])->first();
              if ($get->id_driver==null){
                $this->hapusTiket($chatid, $params);
              }else {
                $message = "Tiket sudah terupdate";
                $response=Telegram::sendMessage([
                  'chat_id'=>$chatid,
                  'text'=>$message
                ]);
              }//end else
            }//end else
          }else{
            $this->updateLog($chatid, $params);
          }
        break;

				/*
				BUAT PESAN DRIVER
				*/
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


				/*
					BUAT UPDATE DRIVER
				*/
				// case $text === '/updatedriver'://Udah bisa
				// 	$this->showUpdateDriver($chatid, $username, $text);
				// 	break;
				// case substr($text,0,7) === '/upddrv':
				// 	$listparams = substr($text,7);
				// 	$params = explode('#',$listparams);
				// 	unset($params[0]);
				// 	$params = array_values($params);

				// 	if(count($params)==1){
				// 		$this->confirmDriver($chatid, $params);
				// 	}elseif(count($params)==2){
				// 		if($params[1]=="set"){
				// 			$this->setPic($chatid, $params);
				// 		}else{
				// 			$this->releaseDriver($chatid, $params);
				// 		}
				// 	}elseif(count($params)==3){
				// 		// $callback_query_id=0;
				// 		$month_input = date("Y-m");
				// 		$this->showCalendar($chatid, $params, $month_input, $callback_query_id);
				// 	}elseif(count($params)==4){
				// 		// $callback_query_id=0;
				// 		$today = strftime('%F');
				// 		if ($params[3]<$today) {
				// 			$this->errorMessage($chatid);
    //         }else {
    //           $this->setLocation($chatid, $params);
    //         }
				// 	}elseif(count($params)==5){
				// 		// $callback_query_id=0;
				// 		$this->saveTheUpdates($chatid, $params, $username);
				// 	}//end elseif

				// 	//$response_txt .= "Mengenal command dan berhasil merespon\n";
				// 	break;

				// case substr($text,0,6) === 'change':
				// 	$params = explode('#',$text);
				// 	unset($params[0]);
				// 	$params = array_values($params);
				// 	$month_input = substr($text,6,7);
				// 	$this->changeCalendar($chatid, $messageid, $month_input, $callback_query_id, $params);
				// 	break;


				default:
				   $this->defaultMessage($chatid, $text, $username);
				   break;
			}//end switch

  }//akhir fungsi respond



	public function webhook()
  {//awal fungsi webhook
		try{//awal try
			$request = Telegram::getWebhookUpdates();

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
				// case $text === '/driver'://udah bisa
				// 	$this->showDriverList($chatid, $username, $text);
				// 	break;




				/*
				BUAT CONFIRM DRIVER SELESAI BERTUGAS
		// 		*/
		case $text === '/selesai'://udah bisa
			$this->tampilTiketDriver($chatid);
			break;

		case substr($text,0,8) === '/confirm':
			$listparams = substr($text,8);
			$params = explode('#',$listparams);
			unset($params[0]);
			$params = array_values($params);

			if(count($params)==1){
				$this->detailTiket($chatid, $params);
			}
			elseif (count($params)==2) {
				$this->konfirmasi($chatid, $params);
			}
			elseif (count($params)==3) {
				$this->updateStatusTiket($chatid, $params);
			}
		break;

				/*
				BUAT UPDATE TIKET
				*/
				case $text === '/updatetiket'://udah bisa
          $this->updateTiket($chatid, $text, $username);
          break;

        case substr($text,0,7) === '/updtkt':
          $listparams = substr($text,7);
          $params = explode('#',$listparams);
          unset($params[0]);
          $params = array_values($params);

          if(count($params)==1){
            $this->showDataTiket($chatid, $params);
          }elseif (count($params)==2) {
            $nomor = $params[0];
            $get=DB::table('tiket')->where(['id'=>$nomor])->first();
            $tanggals=$get->tanggal;
            if ($params[1]==="APPROVE") {
              $result=DB::table('driver')
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
              if ($result->count()>0){
                $nomor = $params[0];
                $get=DB::table('tiket')->where(['id'=>$nomor])->first();
                if ($get->id_driver==null) {
                  $this->setDriver($chatid, $params);
                }else {
                  $message = "Tiket sudah terupdate";
                  $response=Telegram::sendMessage([
                    'chat_id'=>$chatid,
                    'text'=>$message
                  ]);
                }//end else
              }else {
                $this->pesanDriverHabis($chatid);
              }
            }else {
              $nomor = $params[0];
              $get=DB::table('tiket')->where(['id'=>$nomor])->first();
              if ($get->id_driver==null){
                $this->hapusTiket($chatid, $params);
              }else {
                $message = "Tiket sudah terupdate";
                $response=Telegram::sendMessage([
                  'chat_id'=>$chatid,
                  'text'=>$message
                ]);
              }//end else
            }//end else
          }else{
            $this->updateLog($chatid, $params);
          }
        break;

				/*
				BUAT PESAN DRIVER
				*/
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


				/*
					BUAT UPDATE DRIVER
				*/
				// case $text === '/updatedriver'://Udah bisa
				// 	$this->showUpdateDriver($chatid, $username, $text);
				// 	break;
				// case substr($text,0,7) === '/upddrv':
				// 	$listparams = substr($text,7);
				// 	$params = explode('#',$listparams);
				// 	unset($params[0]);
				// 	$params = array_values($params);

				// 	if(count($params)==1){
				// 		$this->confirmDriver($chatid, $params);
				// 	}elseif(count($params)==2){
				// 		if($params[1]=="set"){
				// 			$this->setPic($chatid, $params);
				// 		}else{
				// 			$this->releaseDriver($chatid, $params);
				// 		}
				// 	}elseif(count($params)==3){
				// 		// $callback_query_id=0;
				// 		$month_input = date("Y-m");
				// 		$this->showCalendar($chatid, $params, $month_input, $callback_query_id);
				// 	}elseif(count($params)==4){
				// 		// $callback_query_id=0;
				// 		$today = strftime('%F');
				// 		if ($params[3]<$today) {
				// 			$this->errorMessage($chatid);
    //         }else {
    //           $this->setLocation($chatid, $params);
    //         }
				// 	}elseif(count($params)==5){
				// 		// $callback_query_id=0;
				// 		$this->saveTheUpdates($chatid, $params, $username);
				// 	}//end elseif

				// 	//$response_txt .= "Mengenal command dan berhasil merespon\n";
				// 	break;

				// case substr($text,0,6) === 'change':
				// 	$params = explode('#',$text);
				// 	unset($params[0]);
				// 	$params = array_values($params);
				// 	$month_input = substr($text,6,7);
				// 	$this->changeCalendar($chatid, $messageid, $month_input, $callback_query_id, $params);
				// 	break;


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

	public function showMenu($chatid)//fungsi buat nampilin menu
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

	// public function errorMessage($chatid)
 //  {
 //    $message="Tanggal penugasan sudah kadaluarsa.\n Silakan pilih kembali tanggal penugasan diatas.";
 //    $response=Telegram::sendMessage([
 //      'chat_id'=>$chatid,
 //      'text'=>$message
 //    ]);
 //  }

	// public function showDriverList($chatid, $username, $text)//fungsi buat nampilin data driver
 //  {//awal fungsi
	// 	$message="";
	// 	$result = DB::table('driver')->get();
	// 	$message = "*DAFTAR DRIVER KANWIL* \n\n";
	// 	if ($result->count()>0){
	// 		for ($i=0;$i<$result->count();$i++){
	// 			$message .= "*".$result[$i]->nama."*\n";
	// 			if($result[$i]->status =="Standby"){
	// 				$message .= "Status : Standby\n";
	// 			}else{
	// 				$message .= "Status : ".$result[$i]->status."\n";
	// 			}
	// 			$message .= "\n";
	// 		}//end for
	// 	}//end if

	// 	$response = Telegram::sendMessage([
	// 		'chat_id' => $chatid,
	// 		'parse_mode' => 'markdown',
	// 		'text' => $message
	// 	]);
	// }//akhir fungsi

	// public function showUpdateDriver($chatid, $username, $text)//fungsi buat update driver
 //  {//awal fungsi
	// 	$driver = [];
	// 	$keyboard = [];
	// 	$message="";
	// 	$result = DB::table('driver')->get();
	// 	$message = "*PILIH DRIVER YANG AKAN DI-UPDATE* \n\n";
	// 	$max_col = 2;
	// 	$col =0;
	// 	$driverperrow = [];
	// 	if ($result->count()>0){
	// 		for ($i=0;$i<$result->count();$i++){
	// 			if($col<$max_col){
	// 				$driverperrow[] = Keyboard::inlineButton(['text' => $result[$i]->nama." (".$result[$i]->status.")", 'callback_data' => '/upddrv#'.$result[$i]->id]);
	// 			}else{
	// 				$col=0;
	// 				$driver[] = $driverperrow;
	// 				$driverperrow = [];
	// 				$driverperrow[] = Keyboard::inlineButton(['text' => $result[$i]->nama." (".$result[$i]->status.")", 'callback_data' => '/upddrv#'.$result[$i]->id]);
	// 			}//end else
	// 			$col++;
	// 		}//end for
	// 	}//end if
	// 	if($col>0){
	// 		$col=0;
	// 		$driver[] = $driverperrow;
	// 	}//end if

	// 	$reply_markup = Telegram::replyKeyboardMarkup([
	// 		'resize_keyboard' => true,
	// 		'one_time_keyboard' => true,
	// 	    'inline_keyboard' => $driver
	// 	]);

	// 	$response = Telegram::sendMessage([
	// 	  'chat_id' => $chatid,
	// 	  'parse_mode' => 'markdown',
	// 	  'text' => $message,
	// 	  'reply_markup' => $reply_markup
	// 	]);
	// }//akhir fungsi

	// public function confirmDriver($chatid, $params)
 //  {//awal fungsi
	// 	$message="";
	// 	$keyboard = [];
	// 	$driverid = $params[0];
	// 	$result = DB::table('driver')->where(['id'=>$driverid])->get();
	// 	if ($result->count()>0){
	// 		$message .= "Driver ".$result[0]->nama." ";
	// 		if($result[0]->status == "Terpakai"){
	// 			$message .= "saat ini sedang bertugas";
	// 			$keyboardperrow[] = Keyboard::inlineButton(['text' => 'Selesai Bertugas?', 'callback_data' => '/upddrv#'.$params[0]."#release"]);
	// 		}else{
	// 			$message .= "saat ini kosong \n";
	// 			$keyboardperrow[] = Keyboard::inlineButton(['text' => 'Set Penugasan?', 'callback_data' => '/upddrv#'.$params[0]."#set"]);
	// 		}//akhir else
	// 		$keyboard[] = $keyboardperrow;
	// 		$reply_markup = Telegram::replyKeyboardMarkup([
	// 			'resize_keyboard' => true,
	// 			'one_time_keyboard' => true,
	// 			'inline_keyboard' => $keyboard
	// 		]);
	// 		$response = Telegram::sendMessage([
	// 			'chat_id' => $chatid,
	// 			'text' => $message,
	// 			'reply_markup' => $reply_markup
	// 		]);
	// 	}//akhir if
 //    else{
	// 		$response = Telegram::sendMessage([
	// 			'chat_id' => $chatid,
	// 			'text' => "Data Driver salah"
	// 		]);
	// 	}//akhir else
	// 	$messageId = $response->getMessageId();
	// }//akhir fungsi

	// public function releaseDriver($chatid, $params)
 //  {//awal fungsi
	// 	$result = DB::table('driver')->where(['Id'=>$params[0]])->update(['status'=>"Standby"]);
	// 	$message = "Data Driver berhasil terupdate\n";
	// 	$response = Telegram::sendMessage([
	// 		'chat_id' => $chatid,
	// 		'text' => $message
	// 	]);
	// }//akhir fungsi

	// public function setPic($chatid, $params)//fungsi buat milih bagian kerja atau PIC
 //  {//awal fungsi pic
	// 	$message="";
	// 	$pic = [];
	// 	$driverid = $params[0];
	// 	$piclist = ['LOG','SDM','MRK','LEGAL','OJL','ECH','KONSUMER','AO','BIT','ARK','ADK','RPKB','EBK','PRG','DJS','BRILINK','RTL','MKR','WPO','WPB1','WPB2','WPB3','WPB4','PINWIL','KANPUS','PIHAK LUAR','LAIN-LAIN'];
	// 	$message = "*PILIH PIC YANG PESAN* \n\n";
	// 	$max_col = 4;
	// 	$col =0;
	// 	$picperrow = [];
	// 	for ($i=0;$i<count($piclist);$i++){
	// 		if($col<$max_col){
	// 			$picperrow[] = Keyboard::inlineButton(['text' => $piclist[$i], 'callback_data' => '/upddrv#'.$params[0]."#".$params[1]."#".$piclist[$i]]);
	// 		}else{
	// 			$col=0;
	// 			$pic[] = $picperrow;
	// 			$picperrow = [];
	// 			$picperrow[] = Keyboard::inlineButton(['text' => $piclist[$i], 'callback_data' => '/upddrv#'.$params[0]."#".$params[1]."#".$piclist[$i]]);
	// 		}//end else
	// 		$col++;
	// 	}//end for
	// 	if($col>0){
	// 		$col=0;
	// 		$pic[] = $picperrow;
	// 	}//end if
	// 	$reply_markup = Telegram::replyKeyboardMarkup([
	// 		'resize_keyboard' => true,
	// 		'one_time_keyboard' => true,
	// 	  'inline_keyboard' => $pic
	// 	]);

	// 	$response = Telegram::sendMessage([
	// 	  'chat_id' => $chatid,
	// 	  'parse_mode' => 'markdown',
	// 	  'text' => $message,
	// 	  'reply_markup' => $reply_markup
	// 	]);
	// }//akhir fungsi pic

	// public function showCalendar($chatid, $params, $month_input, $cbid)
 //  {//awal fungsi
	// 	if($cbid != 0){
	// 		$responses = Telegram::answerCallbackQuery([
	// 			'callback_query_id' => $cbid,
	// 			'text' => '',
	// 			'show_alert' => false
	// 		]);
	// 	}//end if

	// 	$message = "*PILIH TANGGAL PENUGASAN*\n";
	// 	$message .= DateTime::createFromFormat('Y-m-d',$month_input."-01")->format("F Y")." \n";
	// 	$calendar = $this->createCalendar($month_input, $params);

	// 	$reply_markup = Telegram::replyKeyboardMarkup([
	// 		'resize_keyboard' => true,
	// 		'one_time_keyboard' => true,
	// 	    'inline_keyboard' => $calendar
	// 	]);

	// 	$response = Telegram::sendMessage([
	// 	  'chat_id' => $chatid,
	// 	  'text' => $message,
	// 	  'parse_mode' => 'markdown',
	// 	  'reply_markup' => $reply_markup
	// 	]);
	// }//akhir fungsi

	// public function changeCalendar($chatid, $messageid, $month_input,$callback_query_id, $params)
 //  {//awal fungsi

	// 	$message = "";
 //    $message = "*PILIH TANGGAL PENUGASAN*\n";
	// 	$message .= DateTime::createFromFormat('Y-m-d',$month_input."-01")->format("F Y")." \n";
	// 	$calendar = $this->createCalendar($month_input, $params);

	// 	$reply_markup = Telegram::replyKeyboardMarkup([
	// 		'resize_keyboard' => true,
	// 		'one_time_keyboard' => true,
	// 	    'inline_keyboard' => $calendar
	// 	]);

	// 	$response = Telegram::editMessageText([
	// 	  'chat_id' => $chatid,
	// 	  'message_id' =>$messageid,
 //      'parse_mode'=>'markdown',
	// 	  'text' => $message,
	// 	  'reply_markup' => $reply_markup
	// 	]);
	// }//akhir fungsi change calendar

	// public function createCalendar($month_input, $params)//fungsi buat bikin kalender
 //  {//awal fungsi create calendar
	// 	$calendar = [];
	// 	$keyboard = [];
	// 	$maxdate = date("t", strtotime($month_input."-01"));
	// 	$startday = date("w", strtotime($month_input."-01"));
	// 	$date = 1;
	// 	$row = 0;
	// 	$calendar = [];
	// 	while($date<=$maxdate){
	// 		$calendarperrow = [];
	// 		for($col=0;$col<7;$col++){
	// 			if((($col<$startday)&&($row==0))||(($date>$maxdate))){
	// 				$calendarperrow[] = Keyboard::inlineButton(['text' => '_', 'callback_data' => '_']);
	// 			}else{
	// 				$calendarperrow[] = Keyboard::inlineButton(['text' => substr("0".strval($date),-2), 'callback_data' => '/upddrv#'.$params[0]."#".$params[1]."#".$params[2]."#".$month_input."-".substr("0".strval($date),-2)]);
	// 				$date++;
	// 			}//end else
	// 		}//end for
	// 		$calendar[] = $calendarperrow;
	// 		$row++;
	// 	}//end while

	// 	$eek = trim($month_input)."-01";
	// 	$prev_date = DateTime::createFromFormat('Y-m-d',$eek)->sub(new DateInterval('P1M'))->format("Y-m");
	// 	$next_date = DateTime::createFromFormat('Y-m-d',$eek)->add(new DateInterval('P1M'))->format("Y-m");

	// 	$calendarperrow = [
	// 		Keyboard::inlineButton(['text' => 'Previous', 'callback_data' => "change".$prev_date.'#'.$params[0]."#".$params[1]."#".$params[2]."#".$month_input."-".substr("0".strval($date),-2)]),
	// 		Keyboard::inlineButton(['text' => 'Next', 'callback_data' => "change".$next_date.'#'.$params[0]."#".$params[1]."#".$params[2]."#".$month_input."-".substr("0".strval($date),-2)])
	// 	];
	// 	$calendar[] = $calendarperrow;

	// 	return $calendar;
	// }//akhir fungsi create calendar

	// public function setLocation($chatid, $params)//fungsi buat milih tujuan kerja
 //  {//awal fungsi
	// 	$message="";
	// 	$location = [];
	// 	$locationperrow = [];
	// 	$driverid = $params[0];
	// 	$locationlist = ['DALAM KOTA', 'LUAR KOTA'];
	// 	$message = "*PILIH LOKASI PENUGASAN* \n\n";
	// 	$max_col = 4;
	// 	$col =0;
	// 	for ($i=0;$i<count($locationlist);$i++){
	// 		if($col<$max_col){
	// 			$locationperrow[] = Keyboard::inlineButton(['text' => $locationlist[$i], 'callback_data' => '/upddrv#'.$params[0]."#".$params[1]."#".$params[2]."#".$params[3]."#".$locationlist[$i]]);
	// 		}else{
	// 			$col=0;
	// 			$location[] = $locationperrow;
	// 			$locationperrow = [];
	// 			$locationperrow[] = Keyboard::inlineButton(['text' => $locationlist[$i], 'callback_data' => '/upddrv#'.$params[0]."#".$params[1]."#".$params[2]."#".$params[3]."#".$locationlist[$i]]);
	// 		}//end else
	// 		$col++;
	// 	}//end for
	// 	if($col>0){
	// 		$col=0;
	// 		$location[] = $locationperrow;
	// 	}//end if
	// 	$reply_markup = Telegram::replyKeyboardMarkup([
	// 		'resize_keyboard' => true,
	// 		'one_time_keyboard' => true,
	// 	    'inline_keyboard' => $location
	// 	]);

	// 	$response = Telegram::sendMessage([
	// 	  'chat_id' => $chatid,
	// 	  'parse_mode' => 'markdown',
	// 	  'text' => $message,
	// 	  'reply_markup' => $reply_markup
	// 	]);
	// }//akhir fungsi

	// public function saveTheUpdates($chatid, $params, $username)
 //  {//awal fungsi save updates
	// 	$idDriver=$params[0];
	// 	$status="";
	// 	if($params[1]=="set"){
	// 		$status= "Terpakai";
	// 	}
	// 	$result = DB::table('log_driver')->insert(['tanggal'=>date('Y-m-d H:i:s'),'Id'=>$params[0],'pic'=>$params[2],'tanggal_mulai'=>$params[3], 'lokasi'=>$params[4]]);
	// 	$result = DB::table('driver')->where(['Id'=>$params[0]])->update(['status'=>$status]);
	// 	$pesan="Hallo, anda telah dipesan oleh bagian ".$params[2]." dengan tujuan ".$params[4]." pada tanggal ".$params[3].". Silakan hubungi @".$username." untuk waktu keberangkatan";
	// 	$message = "Data Driver berhasil terupdate\n";
	// 	$response = Telegram::sendMessage([
	// 		'chat_id' => $chatid,
	// 		'text' => $message
	// 	]);
	// 	$response = Telegram::sendMessage([
	// 	  'chat_id' => 437329516,//kalo mau ke supirnya tinggal diganti @idDriver
	// 	  'parse_mode' => 'markdown',
	// 	  'text' => $pesan
	// 	]);
	// }//akhir fungsi save updates


	// /*
	// 	INI BUAT KODE PESAN DRIVER
	// */

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

		$response = Telegram::sendMessage([
		  'chat_id' => $chatid,
		  'parse_mode' => 'markdown',
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

	public function aturPic($chatid, $params)
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

			$response = Telegram::sendMessage([
				'chat_id' => $chatid,
				'parse_mode' => 'markdown',
				'text' => $message,
				'reply_markup' => $reply_markup
			]);

	}//ini akhir fungsi pic

	public function simpanPesanan($chatid, $params, $username)
  {//awal fungsi
		$status="";
		$syarat=$params[3];
		if($params[3]=='BENAR'){
				$status="";
				$newpemesanan= DB::table('tiket')->insertGetId(array('chatid'=>$chatid, 'username'=>$username, 'pic'=>$params[1], 'tanggal'=>$params[0], 'lokasi'=>$params[2]));
				//DB::table('pemesanan')->insert(['pic'=>$params[1],'username'=>$username,'chatid'=>$chatid,'tanggal'=>$params[0], 'lokasi'=>$params[2]]);
				$pesan="Hallo, ada pemesanan dari bagian ".$params[1]." atas nama ".$username." dengan tujuan ".$params[2]." pada tanggal ".$params[0].". Silakan click /updatetiket untuk memproses tiket yang ada";
				$message = "*Pemesanan Berhasil. Nomor tiket anda adalah : $newpemesanan*\n";
				//$result=


				$response = Telegram::sendMessage([
					'chat_id' => $chatid,
					'parse_mode' => 'markdown',
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
			$response=Telegram::sendMessage([
				'chat_id'=>$chatid,
				'text'=>$message
			]);
		}//akhir else
	}//akhir fungsi



	/*
		BUAT UPDATE TIKET
		KALO ERROR HARAP BERSABAR
		HAHAHAHAA
	*/
	public function pesanDriverHabis($chatid)
  {
    $message="Driver penuh";
    $response=Telegram::sendMessage([
      'chat_id'=>$chatid,
      'text'=>$message
    ]);
  }//akhir fungsi pesan driver habis

  public function hapusTiket($chatid, $params)
  {
    $statusTiket="SELESAI";
    $nomor=$params[0];
    DB::table('tiket')->where(['id'=>$nomor])->update(['status'=>$statusTiket]);
    $message="Tiket dengan nomor tiket ".$nomor. " telah berhasil dihapus.";
    $response = Telegram::sendMessage([//buat ngirim ke admin
			'chat_id' => $chatid,
			'text' => $message
		]);
  }//akhir fungsi hapus tiket

  public function updateLog($chatid, $params)
  {//awal fungsi updateLog
    $sekarang=date('Y-m-d H:i:s');
		$nomor=$params[0];
    $idDriver=$params[2];
    // $nomor='13';
    // $idDriver='549021135';
		$statusDriver="Terpakai";
    $statusTiket="SELESAI";
    $get = DB::table('tiket')->where(['id'=>$nomor])->first();
    $result = DB::table('driver')->where(['id'=>$idDriver])->first();
    DB::table('log_driver')->insert(['tanggal'=>$sekarang,'id'=>$idDriver,'pic'=>$get->pic,'tanggal_mulai'=>$get->tanggal, 'lokasi'=>$get->lokasi]);
    DB::table('tiket')->where(['id'=>$nomor])->update(['id_driver'=>$idDriver]);
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
		  'chat_id' => $params[2],//kalo mau ke supirnya tinggal diganti @idDriver
		  'text' => $pesan
		]);
	}//akhir fungsi updateLog

  public function setDriver($chatid, $params)//fungsi buat update driver
  {//awal fungsi
    $nomor=$params[0];
    $get=DB::table('tiket')->where(['id'=>$nomor])->first();
    if (($get->status)===null) {
      $driver = [];
  		$keyboard = [];
  		$message="";
      $tanggals=$get->tanggal;
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
  					$driverperrow[] = Keyboard::inlineButton(['text' => $result[$i]->nama, 'callback_data' => '/updtkt#'.$params[0]."#".$params[1]."#".$result[$i]->id]);
  				}else{
  					$col=0;
  					$driver[] = $driverperrow;
  					$driverperrow = [];
  					$driverperrow[] = Keyboard::inlineButton(['text' => $result[$i]->nama, 'callback_data' => '/updtkt#'.$params[0]."#".$params[1]."#".$result[$i]->id]);
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
    }//endif
    else {
      $message = "*Tiket sudah tidak berlaku*";

      $response = Telegram::sendMessage([
        'chat_id'=>$chatid,
        'parse_mode'=>'markdown',
        'text'=>$message
      ]);
    }
	}//akhir fungsi set driver

  public function updateTiket($chatid, $text, $username)//udah bisa
  {//awal fungsi update tiket
    $today=date('Y-m-d H:i:s');
    $result = DB::table('tiket')->where(['status'=>null])
                                ->where(['id_driver'=>null])
                                ->get();
    if ($result->count()>0){
      $message = "*PILIH TIKET YANG AKAN DI-UPDATE* \n\n";
  		$max_col = 1;
  		$col =0;
  		if ($result->count()>0){
  			for ($i=0;$i<$result->count();$i++){
  				if($col<$max_col){
  					$tiketperrow[] = Keyboard::inlineButton(['text' =>"NOMOR TIKET : ".$result[$i]->id." (".$result[$i]->pic.") (".$result[$i]->tanggal.")", 'callback_data' => '/updtkt#'.$result[$i]->id]);
  				}else{
  					$col=0;
  					$tiket[] = $tiketperrow;
  					$tiketperrow = [];
  					$tiketperrow[] = Keyboard::inlineButton(['text' =>"NOMOR TIKET : ".$result[$i]->id." (".$result[$i]->pic.") (".$result[$i]->tanggal.")", 'callback_data' => '/updtkt#'.$result[$i]->id]);
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

  }else {
    $message = "*TIKET KOSONG*";
    $response = Telegram::sendMessage([
  	  'chat_id' => $chatid,
  	  'parse_mode' => 'markdown',
  	  'text' => $message
  	]);
    }//end else
  }//akhir fungsi update tiket

  public function showDataTiket($chatid, $params)//udah bisa tampil
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
      Keyboard::inlineButton(['text' => 'APPROVE', 'callback_data' => '/updtkt#'.$params[0]."#APPROVE"]),
      Keyboard::inlineButton(['text' => 'HAPUS TIKET', 'callback_data' => '/updtkt#'.$params[0]."#HAPUS TIKET"])
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



	/*
		INI KODE YAA
		Ini Buat Confirm selesai Driver atau confirm driver
	*/

	public function tampilTiketDriver($chatid)
  {
    $today=date('Y-m-d');
    $result=DB::table('tiket')->where('id_driver',"=",$chatid)
                              ->where('status',"=",null)
                              ->where('tanggal','<=',$today)
                              ->get();
      if ($result->count()>0){
        $message = "*PILIH TIKET YANG AKAN ANDA KONFIRMASI* \n\n";
    		$max_col = 1;
    		$col =0;
    		if ($result->count()>0){
    			for ($i=0;$i<$result->count();$i++){
    				if($col<$max_col){
    					$tiketperrow[] = Keyboard::inlineButton(['text' =>"NOMOR TIKET : ".$result[$i]->id."(".$result[$i]->pic.") (".$result[$i]->tanggal.")", 'callback_data' => '/confirm#'.$result[$i]->id]);
    				}else{
    					$col=0;
    					$tiket[] = $tiketperrow;
    					$tiketperrow = [];
    					$tiketperrow[] = Keyboard::inlineButton(['text' =>"NOMOR TIKET : ".$result[$i]->id."(".$result[$i]->pic.") (".$result[$i]->tanggal.")", 'callback_data' => '/confirm#'.$result[$i]->id]);
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
      }
      else {
        $response = Telegram::sendMessage([
          'chat_id' => $chatid,
          'text' => "Anda masih dalam status STANDBY"
        ]);
      }//endelse
  }//end function

  public function detailTiket($chatid, $params)
  {
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
      Keyboard::inlineButton(['text' => 'KONFIRMASI SELESAI', 'callback_data' => '/confirm#'.$params[0]."#DONE"]),
      Keyboard::inlineButton(['text' => 'KEMBALI', 'callback_data' => '/selesai'])
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
  }//end function

  public function konfirmasi($chatid, $params)
  {
    $nomor=$params[0];
		$result = DB::table('tiket')->where(['id'=>$nomor])->first();
    $username=$result->username;
    $pesanDriver="Terima kasih atas konfirmasi dan kerjasama anda.";
    $message="Driver atas nama ".$username." telah selesai mengerjakan tugas. Silakan click disini untuk mengubah status driver yang bersangkutan menjadi stanby";
    $inlineLayout = [[
			Keyboard::inlineButton(['text' => 'DISINI', 'callback_data' => '/confirm#'.$params[0]."#".$params[1]."#SELESAI"])
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
  }//akhir fungsi

  public function updateStatusTiket($chatid, $params)
  {//awal fungsi updateLog
    $nomor=$params[0];
		$statusTiket="SELESAI";
    DB::table('tiket')->where(['id'=>$nomor])->update(['status'=>$statusTiket]);
    $message="Status driver telah terupdate";

    $response = Telegram::sendMessage([//buat ngirim ke pemesan
			'chat_id' => $chatid,
			'text' => $message
		]);
	}//akhir fungsi updateLog

}//akhir kelas
?>
