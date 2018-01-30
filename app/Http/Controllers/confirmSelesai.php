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
        $this->konfirmasi($chatid, $username, $text);
        break;

      case substr($text,0,8) === '/confirm':
        $listparams = substr($text,8);
        $params = explode('#',$listparams);
        unset($params[0]);
        $params = array_values($params);

        if(count($params)==1){
          $this->updateStatusDriver($chatid, $params);
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
          $this->konfirmasi($chatid, $username, $text);
          break;

        case substr($text,0,8) === '/confirm':
          $listparams = substr($text,8);
          $params = explode('#',$listparams);
          unset($params[0]);
          $params = array_values($params);

          if(count($params)==1){
            $take=DB::table('driver')->where(['id'=>$chatid])->first();
            if ($take->status==='Terpakai') {
              $this->updateStatusDriver($chatid, $params);
            }else {
              $response = Telegram::sendMessage([
          			'chat_id' => $chatid,
          			'text' => "Anda masih dalam status STANDBY"
          		]);
            }

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
		$statusDriver="Standby";
    DB::table('driver')->where(['id'=>$idDriver])->update(['status'=>$statusDriver]);
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
