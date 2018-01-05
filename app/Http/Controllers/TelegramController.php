<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Telegram\Bot\Laravel\Facades\Telegram;


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

      $response = Telegram::sendMessage([
        'chat_id' => $chatid,
        'text' => 'Hey! This is bot sending you the first message :)'
      ]);
    }

    public function setWebHook()
    {
      $response = Telegram::setWebhook(['url' => 'https://b0ca66eb.ngrok.io/502539981:AAE7FDMraFwOV40U8NNR4MLpIkmnE1J7r84/webhook']);
      return $response;
    }

    public function showMenu($chatid, $info = null){
        $message = '';
        if($info !== null){
            $message .= $info.chr(10);
        }
        $message .=  '/website'.chr(10);
        $message .= '/contact'.chr(10);

        $response = Telegram::sendMessage([
            'chat_id' => $chatid,
            'text' => $message
        ]);
    }

    public function showWebsite($chatid){
        $message = 'http://google.com';

        $response = Telegram::sendMessage([
            'chat_id' => $chatid,
            'text' => $message
        ]);
    }

    public function showContact($chatid){
        $message = 'info@jqueryajaxphp.com';

        $response = Telegram::sendMessage([
            'chat_id' => $chatid,
            'text' => $message
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
      		case '/contact';
      			$this->showContact($chatid);
      			break;
      		default:
      			$info = 'I do not understand what you just said. Please choose an option';
      			$this->showMenu($chatid, $info);
	      }
    }

    }
