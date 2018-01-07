<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Telegram\Bot\Laravel\Facades\Telegram;

class removeWebhook extends Controller
{
  public function removeWebhook(){
    $response = Telegram::removeWebhook();
  }
}
