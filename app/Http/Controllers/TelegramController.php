<?php

namespace App\Http\Controllers;
use Telegram\Bot\Api;
use Telegram\Bot\Keyboard\Keyboard;
use Illuminate\Support\Facades\DB;
use Telegram\Bot\Laravel\Facades\Telegram;
use App\Http\Requests;
use App\Http\Controllers\Controller;
use Exception;
use DateTime;
use DateInterval;

class TelegramController extends Controller
{
    public function getMe()
    {
      $respon= Telegram::getMe();
      return $respon;
    }

    public function getUpdates()
    {
      $respon= Telegram::getUpdates();
      return $respon;
    }

    public function respon()
    {
      $response = Telegram::getUpdates();
      $request = collect(end($response)); // fetch the last request from the collection
      $chatid = $request['message']['chat']['id']; // get chatid from request
      $text = $request['message']['text']; // get the user sent text
      // $time = $request['message'][''];

      $response = Telegram::sendMessage([
        'chat_id' => $chatid,
        'text' => 'Hey! This is bot sending you the first message :)'
      ]);
    }

    public function showMenu($chatid, $info = null)
    {
        $message = '';
        if($info !== null){
            $message .= $info.chr(10);
        }
        $message .=  '/pesandriver'.chr(10);


        $response = Telegram::sendMessage([
            'chat_id' => $chatid,
            'text' => $message
        ]);
    }

    // public function showWebsite($chatid)
    // {
    //     $message = 'http://google.com';
    //
    //     $response = Telegram::sendMessage([
    //         'chat_id' => $chatid,
    //         'text' => $message
    //     ]);
    // }
    //
    // public function showContact($chatid)
    // {
    //     $message = 'info@jqueryajaxphp.com';
    //
    //     $response = Telegram::sendMessage([
    //         'chat_id' => $chatid,
    //         'text' => $message
    //     ]);
    // }
    //
    // public function showTime($chatid)
    // {
    //     // $response = Telegram::getUpdates();
    //     // $request = collect(end($response));
    //     // $nama= $request['message']['chat']['first_name'];
    //     $message = "hai , waktu lokal bot sekarang adalah :\n";
    //     $message .= date("d M Y")."\nPukul ".date("H:i:s");
    //
    //     $response = Telegram::sendMessage([
    //         'chat_id' => $chatid,
    //         'text' => $message
    //     ]);
    // }


  public function createCalendar($month_input, $params)
  {
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
					$calendarperrow[] = Keyboard::inlineButton(['text' => substr("0".strval($date),-2), 'callback_data' => '/upddrv#'.$params[0]."#".$params[1]."#".$params[2]."#".$month_input."-".substr("0".strval($date),-2)]);
					$date++;
				}
			}
			$calendar[] = $calendarperrow;
			$row++;
		}

		$eek = trim($month_input)."-01";
		$prev_date = DateTime::createFromFormat('Y-m-d',$eek)->sub(new DateInterval('P1M'))->format("Y-m");
		$next_date = DateTime::createFromFormat('Y-m-d',$eek)->add(new DateInterval('P1M'))->format("Y-m");

		$calendarperrow = [
			Keyboard::inlineButton(['text' => 'Previous', 'callback_data' => "change".$prev_date]),
			Keyboard::inlineButton(['text' => 'Next', 'callback_data' => "change".$next_date])
		];
		$calendar[] = $calendarperrow;

		return $calendar;
	}

  public function showCalendar($chatid, $params, $month_input, $cbid)
  {
		if($cbid != 0){
			$responses = Telegram::answerCallbackQuery([
				'callback_query_id' => $cbid,
				'text' => '',
				'show_alert' => false
			]);
		}

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
	}



    public function webhook(Request $request)
    {

	      $chatid = $request['message']['chat']['id'];
      	$text = $request['message']['text'];

      	switch($text)
        {
      		case '/start':
      			$this->showMenu($chatid);
      			break;
      		case '/menu':
      		    $this->showMenu($chatid);
      		    break;
      		case '/website':
      			$this->showWebsite($chatid);
      			break;
          case '/time':
            $this->showTime($chatid);
            break;

      		case '/contact';
      			$this->showContact($chatid);
      			break;

          case '/pesandriver':
            $this->showCalendar($chatid, $params, $month_input, $cbid);
            break;

      		default:
      			$info = 'Hallo, silakan pilih opsi berikut :';
      			$this->showMenu($chatid, $info);
	      }
    }
    }
