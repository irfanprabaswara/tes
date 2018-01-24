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
  public function tes(){
    $result = DB::table('driver')->get();
    $message = "*DAFTAR DRIVER KANWIL* \n\n";
    if ($result->count()>0){
      for ($i=0;$i<$result->count();$i++){
        $message .= "*".$result[$i]->nama."*\n";
        if($result[$i]->status ==""){
          $message .= "Status : Kosong\n";
        }else{
          $message .= "Status : ".$result[$i]->status."\n";
        }
        $message .= "\n";
      }//end for
    }//end if
  }
}//akhir kelas
?>
