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

class updatetiket extends Controller
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
          $this->setDriver($chatid, $params);
        }else{
          $this->updateLog($chatid, $params);
        }
      //   //$response_txt .= "Mengenal command dan berhasil merespon\n";
      //   break;
      //
      // case substr($text,0,6) === 'change':
      //   $month_input = substr($text,6,7);
      //   $this->changeCalendar($chatid, $messageid, $month_input, $callback_query_id);
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
            $this->setDriver($chatid, $params);
          }else{
            $this->updateLog($chatid, $params);
          }
        //   //$response_txt .= "Mengenal command dan berhasil merespon\n";
        //   break;
        //
        // case substr($text,0,6) === 'change':
        //   $month_input = substr($text,6,7);
        //   $this->changeCalendar($chatid, $messageid, $month_input, $callback_query_id);
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
		$max_col = 1;
		$col =0;
		if ($result->count()>0){
			for ($i=0;$i<$result->count();$i++){
				if($col<$max_col){
					$tiketperrow[] = Keyboard::inlineButton(['text' =>"NOMOR TIKET : ".$result[$i]->no_tiket.",  TANGGAL PENGGUNAAN : ".$result[$i]->tanggal, 'callback_data' => '/updtkt#'.$result[$i]->no_tiket]);
				}else{
					$col=0;
					$tiket[] = $tiketperrow;
					$tiketperrow = [];
					$tiketperrow[] = Keyboard::inlineButton(['text' =>"NOMOR TIKET : ".$result[$i]->no_tiket.",  TANGGAL PENGGUNAAN : ".$result[$i]->tanggal, 'callback_data' => '/updtkt#'.$result[$i]->no_tiket]);
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

  public function defaultMessage($chatid, $text, $username) //ini untuk menampilkan pesan default
  {
		$message = "Mau apa hayo? Bingung? cek /menu";
		$response = Telegram::sendMessage([
			'chat_id' => $chatid,
			// 'parse_mode' => 'markdown',
			'text' => $message
		]);
		$response = Telegram::sendMessage([
			'chat_id' => $chatid,
			// 'parse_mode' => 'markdown',
			'text' => "akun : ".$username." telah mengirim pesan ".$text." ke bot anda"
		]);
	}//ini akhir fungsi

}//akhir kelas


?>
