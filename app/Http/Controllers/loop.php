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


class loop extends Controller
{//awal kelas
  	public function respond()
    {//awal fungsi respond
		$telegram = new Api (env('TELEGRAM_BOT_TOKEN'));
		$request = Telegram::getUpdates();
		$request = collect(end($request));

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
      case $text === '/pesan':
        $this->setPic($chatid);
        break;
      default://buat pesan default
         $info = 'I do not understand what you just said. Please choose an option';
         $this->showMenu($chatid, $info);
         break;
		}//akhir switch
		return $request;
	}//akhir fungsi respond

  public function setPic($chatid)
	{//awal fungsi pic
		$message="";
		$pic = [];
		$piclist = ['LOG','SDM','MRK','LEGAL','OJL','ECH','KONSUMER','AO','BIT','ARK','ADK','RPKB','EBK','PRG','DJS','BRILINK','RTL','MKR','WPO','WPB1','WPB2','WPB3','WPB4','PINWIL','KANPUS','PIHAK LUAR','LAIN-LAIN'];
		$message = "*PILIH PIC YANG PESAN* \n\n";
		$max_col = 4;
		$col =0;
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

}//akhir kelas
?>
