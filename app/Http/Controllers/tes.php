<?php
namespace App\Http\Controllers;

use Illuminate\Support\Facades\DB;

class tes extends Controller
{//awal kelas

  public function show()
  {
    $result=DB::table('pemesanan')->get();

    return $result;
  }

  public function respond(){
  $telegram = new Api (env('TELEGRAM_BOT_TOKEN'));
  $request = Telegram::getUpdates();
  $request = collect(end($request));

    $chatid = $request['message']['chat']['id'];
    $text = $request['message']['text'];
    $username=$request['message']['chat']['username'];


    switch($text)
    {//mulai switch
      case $text === '/start':
        $this->defaultMessage($chatid, $text, $username);
        break;
      case $text === '/updatetiket'://udah bisa
        $this->updateTiket($chatid, $text, $username);
        break;
      // case $text==='/tiket'://udah bisa
      //   $this->showDataTiket($chatid);
      //   break;
      // case $text==='/urus'://udah bisa
      //   $this->setDriver($chatid, $username, $text);
      //   break;

      case substr($text,0,7) === '/updtkt':
        $listparams = substr($text,7);
        $params = explode('#',$listparams);
        unset($params[0]);
        $params = array_values($params);

        if(count($params)==1){
          $this->showDataTiket($chatid, $params);
        }elseif(count($params)==2){
          $this->setDriver($chatid, $params);
        }
      //   //$response_txt .= "Mengenal command dan berhasil merespon\n";
      //   break;
      //
      // case substr($text,0,6) === 'change':
      //   $month_input = substr($text,6,7);
      //   $this->changeCalendar($chatid, $messageid, $month_input, $callback_query_id);
      //   break;

      default:
         $this->defaultMessage($chatid, $text, $username);
         break;
    }//end switch
  }

}//akhir kelas

?>
