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
use App\Http\Controllers\Master;
//PERLU DIPERHATIKAN

class coba extends Controller
{//awal kelas
  public function ceklalala()
  {
    $m=new Master();
    $cuk = $m->lalala();
    return $cuk;
  }


}//akhir kelas
?>
