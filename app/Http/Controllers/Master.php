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
use App\Pemesanan;
use App\Http\Controllers\updatetiket;
use App\Http\Controllers\pesandriver;
use App\Http\Controllers\updatedriver;
use App\Http\Controllers\confirmSelesai;
use App\Http\Controllers\confirmSelesai;
//PERLU DIPERHATIKAN

class master extends Controller
{//awal kelas

	public function webhook()
  {//awal fungsi webhook
		try{//awal try
			$request = Telegram::getWebhookUpdates();

			if($request->isType('callback_query')){
				$query = $request->getCallbackQuery();
				$text = $query->getData();
				$chatid = $query->getMessage()->getChat()->getId();
				$messageid = $query->getMessage()->getMessageId();
				$username=$query->getMessage()->getChat()->getUsername();
				$callback_query_id = $query->getId();
			}else{
				$chatid = $request->getMessage()->getChat()->getId();
				$text = $request->getMessage()->getText();
				$username=$request->getMessage()->getChat()->getUsername();
				$callback_query_id = 0;
			}//end else

			switch($text)
      {//mulai switch
				$m=new Master();
				case $text === '/start'://udah bisa
					$this->showWelcomeMessage($chatid);
					break;
				case $text==='/menu'://udah bisa
					$this->showMenu($chatid);
					break;
				case $text === 'website'://udah bisa
				   $this->showWebsite($chatid, $callback_query_id);
					   break;
				case $text === 'contact'://udah bisa
				   $this->showContact($chatid, $callback_query_id);
				   break;
				case $text === '/driver'://udah bisa
					$this->showDriverList($chatid, $username, $text);
					break;
				case $text === '/updatedriver'://Udah bisa
					$this->showUpdateDriver($chatid, $username, $text);
					break;
				case $text === '/pesandriver'://udah bisa
					$month_input = date("Y-m");
					$cekDriver=DB::table('driver')->where(['status'=>'Standby'])->get();
					if ($cekDriver->count()>0) {
						$this->tampilCalendar($chatid, $month_input);
					}else {
						$message="*MAAF, DRIVER PENUH*";
						$response=Telegram::sendMessage([
							'chat_id'=>$chatid,
							'text'=>$message,
							'parse_mode'=>'markdown'
						]);
					}
					break;
				case $text === '/updatetiket'://udah bisa
					$this->updateTiket($chatid, $text, $username);
					break;
				case $text === '/selesai'://BUAT CONFIRM DRIVER SELESAI BERTUGAS
	         $this->konfirmasi($chatid, $username, $text);
	         break;
				//BUAT CONFIRM DRIVER SELESAI BERTUGAS
	      case substr($text,0,8) === '/confirm':
	         $listparams = substr($text,8);
	         $params = explode('#',$listparams);
	         unset($params[0]);
	         $params = array_values($params);

					 if(count($params)==1){
             $take=DB::table('driver')->where(['id'=>$chatid])->first();
             if ($take->status==='Terpakai') {
               $this->updateStatusDriver($chatid, $params);
             }else {
               $response = Telegram::sendMessage([
           			'chat_id' => $chatid,
           			'text' => "Driver masih dalam status STANDBY"
           		]);
             }

           }
         break;

				//BUAT UPDATE TIKET
				case substr($text,0,7) === '/updtkt':
          $listparams = substr($text,7);
          $params = explode('#',$listparams);
          unset($params[0]);
          $params = array_values($params);

          if(count($params)==1){
            $this->showDataTiket($chatid, $params);
          }elseif (count($params)==2) {
            if ($params[1]==="APPROVE") {
              $result = DB::table('driver')->where(['status'=>'Standby'])->get();
              if ($result->count()>0){
              	$this->setDriver($chatid, $params);
            	}else {
              	$this->pesanDriverHabis($chatid);
            	}
            }else {
              $this->hapusTiket($chatid, $params);
            }
          }else{
            $this->updateLog($chatid, $params);
          }
        break;

				//BUAT PESAN DRIVER
				case substr($text,0,7) === '/psndrv':
					$listparams = substr($text,7);
					$params = explode('#',$listparams);
					unset($params[0]);
					$params = array_values($params);

					if(count($params)==1){
						$apaya=substr($params[0],0,4);
						if ($apaya === 'ubah') {
							$month_input = substr($params[0],4,7);
							$this->ubahCalendar($chatid, $messageid, $month_input, $params);
						}//endif
						else {
							$today = strftime('%F');
							if ($params[0]<$today) {
								$this->pesanError($chatid);
							}else {
								$this->aturPic($chatid, $params);
							}//end else
					}//end else
					}elseif(count($params)==2){
							$this->lokasi($chatid, $params);
					}elseif(count($params)==3){
						$this->cekPesan($chatid, $params);
					}elseif (count($params)==4) {
						$this->simpanPesanan($chatid, $params, $username);
					}
					//$response_txt .= "Mengenal command dan berhasil merespon\n";
					break;

				//BUAT UPDATE DRIVER
				case substr($text,0,7) === '/upddrv':
					$listparams = substr($text,7);
					$params = explode('#',$listparams);
					unset($params[0]);
					$params = array_values($params);

					if(count($params)==1){
						$this->confirmDriver($chatid, $params);
					}elseif(count($params)==2){
						if($params[1]=="set"){
							$this->setPic($chatid, $params);
						}else{
							$this->releaseDriver($chatid, $params);
						}
					}elseif(count($params)==3){
						// $callback_query_id=0;
						$month_input = date("Y-m");
						$this->showCalendar($chatid, $params, $month_input, $callback_query_id);
					}elseif(count($params)==4){
						// $callback_query_id=0;
						$today = strftime('%F');
						if ($params[3]<$today) {
							$this->errorMessage($chatid);
            }else {
              $this->setLocation($chatid, $params);
            }
					}elseif(count($params)==5){
						// $callback_query_id=0;
						$this->saveTheUpdates($chatid, $params, $username);
					}//end elseif

					//$response_txt .= "Mengenal command dan berhasil merespon\n";
					break;

				case substr($text,0,6) === 'change':
					$params = explode('#',$text);
					unset($params[0]);
					$params = array_values($params);
					$month_input = substr($text,6,7);
					$this->changeCalendar($chatid, $messageid, $month_input, $callback_query_id, $params);
					break;
				case substr($text,0,4) === 'ubah':
					$month_input = substr($text,4,7);
					$this->buatCalendar($chatid, $messageid, $month_input, $callback_query_id);
					break;
				default:
				   $this->defaultMessage($chatid, $text, $username);
				   break;
			}//end switch
		}catch (\Exception $e) {
			Telegram::sendMessage([
				'chat_id' => $chatid,
				'text' => "Reply ".$e->getMessage()
			]);
		}//end catch
	}//akhir fungsi webhook

}//akhir fungsi
?>
