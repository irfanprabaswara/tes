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

class confirmSelesai extends Controller
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

  public function tampilTiketDriver($chatid)
  {
    $response = Telegram::sendMessage([
      'chat_id' => $chatid,
      'text' => "pertama"
    ]);
    $today=date('Y-m-d');
    $result=DB::table('tiket')->where('id_driver',"=",$chatid)
                              ->where('status',"=",null)
                              ->where('tanggal','<',$today)
                              ->get();
    $response = Telegram::sendMessage([
            'chat_id' => $chatid,
            'text' => "KEDUA"
    ]);
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
      Keyboard::inlineButton(['text' => 'KONFIRMASI SELESAI', 'callback_data' => '/confirm#'.$params[0]."#DONE"])
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

}//akhir kelas

?>
