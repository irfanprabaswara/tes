<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Telegram\Bot\Laravel\Facades\Telegram;

class setWebhook extends Controller
{
  public function setWebHook()
  {
    $response = Telegram::setWebhook(['url' => 'https://25d3d1c3.ngrok.io/502539981:AAE7FDMraFwOV40U8NNR4MLpIkmnE1J7r84/webhook']);
    return $response;
  }
}
