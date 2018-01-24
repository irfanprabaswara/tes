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
        // case $text === '/updatetiket'://udah bisa
        //   $this->setPic($chatid);
        //   break;

        // case substr($text,0,7) === '/updtkt':
        //   $listparams = substr($text,7);
        //   $params = explode('#',$listparams);
        //   unset($params[0]);
        //   $params = array_values($params);
        //
        //   if(count($params)==1){
        //     $this->showCalendar($chatid, $params, $month_input, $callback_query_id);
        //   }elseif(count($params)==2){
        //     $this->setLocation($chatid, $params);
        //   }elseif(count($params)==3){
        //     $this->saveTheUpdates($chatid, $params, $username);
        //   }
        //   //$response_txt .= "Mengenal command dan berhasil merespon\n";
        //   break;
        //
        // case substr($text,0,6) === 'change':
        //   $month_input = substr($text,6,7);
        //   $this->changeCalendar($chatid, $messageid, $month_input, $callback_query_id);
        //   break;

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
