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

class loop extends Controller
{//awal kelas

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
  			// case $text === '/calendar'://Udah bisa
        //   $month_input = date("Y-m");
  			// 	$this->showCalendar($chatid, $month_input);
  			// 	break;
        case $text==='/tes':
          $this->pesanTes($chatid);
          break;
  			/*BUAT UPDATE DRIVER*/
  			case substr($text,0,7) === '/upddrv':
  				$listparams = substr($text,7);
  				$params = explode('#',$listparams);
  				unset($params[0]);
  				$params = array_values($params);

  				if(count($params)==1){
            $this->pesanBingung($chatid, $params);
  				}elseif (count($params)==2) {
            $month_input = date("Y-m");
  					$this->showCalendar($chatid, $month_input, $params);
          }elseif (count($params)==3) {
            $this->showTanggal($chatid, $params);
          }
  				break;
        case substr($text,0,6) === 'change':
    			$month_input = substr($text,6,7);
          // changeCalendar($chatid, $messageid, $month_input, $params)
    			$this->changeCalendar($chatid, $messageid, $month_input, $callback_query_id);
    			break;
        default :
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

  // public function respond()
  // {
  // $telegram = new Api (env('TELEGRAM_BOT_TOKEN'));
  // $request = Telegram::getUpdates();
  // $request = collect(end($request));
  //
  //   $chatid = $request['message']['chat']['id'];
  //   $text = $request['message']['text'];
  //   $username=$request['message']['chat']['username'];
  //
  //   switch($text)
  //   {//mulai switch
  //     // case $text === '/calendar'://Udah bisa
  //     //   $month_input = date("Y-m");
  //     // 	$this->showCalendar($chatid, $month_input);
  //     // 	break;
  //     case $text==='/tes':
  //       $this->pesanTes($chatid);
  //       break;
  //     /*BUAT UPDATE DRIVER*/
  //     case substr($text,0,7) === '/upddrv':
  //       $listparams = substr($text,7);
  //       $params = explode('#',$listparams);
  //       unset($params[0]);
  //       $params = array_values($params);
  //
  //       if(count($params)==1){
  //         $this->pesanBingung($chatid, $params);
  //       }elseif (count($params)==2) {
  //         $month_input = date("Y-m");
  //         $this->showCalendar($chatid, $month_input, $params);
  //       }elseif (count($params)==3) {
  //         $this->showTanggal($chatid, $params);
  //       }
  //       break;
  //     case substr($text,0,6) === 'change':
  //       $month_input = substr($text,6,7);
  //       // changeCalendar($chatid, $messageid, $month_input, $params)
  //       $this->changeCalendar($chatid, $messageid, $month_input, $callback_query_id);
  //       break;
  //   }//end switch
  // }//akhir fungsi respond

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

  public function pesanBingung($chatid, $params)
  {
    $message="BINGUNG";
    // this will create keyboard buttons for users to touch instead of typing commands
    $inlineLayout = [[
      Keyboard::inlineButton(['text' => 'BINGUNG', 'callback_data' => '/upddrv#'.$params[0]."#".$message])
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

  public function pesanTes($chatid)
  {
      $message="TES";
  		// this will create keyboard buttons for users to touch instead of typing commands
  		$inlineLayout = [[
  			Keyboard::inlineButton(['text' => 'TES', 'callback_data' => '/upddrv#'.$message])
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

	public function showCalendar($chatid, $month_input, $params)//udah bener
  {//awal fungsi
		// if($cbid != 0){
		// 	$responses = Telegram::answerCallbackQuery([
		// 		'callback_query_id' => $cbid,
		// 		'text' => '',
		// 		'show_alert' => false
		// 	]);
		// }//end if

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
	}//akhir fungsi

	public function changeCalendar($chatid, $messageid, $month_input, $params)//masih error
  {//awal fungsi
		// if($cbid != 0){
		// 	$responses = Telegram::answerCallbackQuery([
		// 		'callback_query_id' => $cbid,
		// 		'text' => '',
		// 		'show_alert' => false
		// 	]);
		// }//end if
    // $idPesan=$messageId-1;
    $get="";
		$message = "";
		$message .= DateTime::createFromFormat('Y-m-d',$month_input."-01")->format("F Y")." \n";
		$calendar = $this->createCalendar($month_input, $params);

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
					$calendarperrow[] = Keyboard::inlineButton(['text' => substr("0".strval($date),-2), 'callback_data' => '/upddrv#'.$params[0]."#".$params[1]."#".$month_input."-".substr("0".strval($date),-2)]);
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

  public function showTanggal($chatid, $params)
  {
    $message = $params[0]." ".$params[1]." ".$params[2];
    $response = Telegram::sendMessage([
		  'chat_id' => $chatid,
		  'text' => $message
		]);
  }

}//akhir kelas
?>
