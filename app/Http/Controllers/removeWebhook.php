<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class removeWebhook extends Controller
{
  public function removeWebhook(){
    $response = Telegram::removeWebhook();
  }
}
