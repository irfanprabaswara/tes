<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class setWebhook extends Controller
{
  public function setWebHook()
  {
    $response = Telegram::setWebhook(['url' => 'https://0181eb8d.ngrok.io/502539981:AAE7FDMraFwOV40U8NNR4MLpIkmnE1J7r84/webhook']);
    return $response;
  }
}
