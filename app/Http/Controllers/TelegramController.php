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
    }
