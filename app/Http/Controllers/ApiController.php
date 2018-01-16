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

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class ApiController extends Controller
{
    public function me(){
    	$telegram = new Api(env('TELEGRAM_BOT_TOKEN'));
    	$response = $telegram->getMe();
    	return $response;
    }

	public function updates(){
		$telegram = new Api(env('TELEGRAM_BOT_TOKEN'));
		$response = $telegram->getUpdates();
		return $response;
	}

  //PERLU DIPERHATIKAN 502539981:AAE7FDMraFwOV40U8NNR4MLpIkmnE1J7r84/webhook punya kanwil 95bb24ec.ngrok.io/kanwilbot/public
	public function setWebhook(){
		$response = Telegram::setWebhook(['url' => 'https://2be07ce5.ngrok.io/502539981:AAE7FDMraFwOV40U8NNR4MLpIkmnE1J7r84/webhook',]);
		dd($response);
	}

	public function unsetWebhook()
	{
		$response = Telegram::removeWebhook();
		dd($response);
	}

	public function webhook(){
		$updates = Telegram::getWebhookUpdates();
		$chatid = $updates->getMessage()->getChat()->getId();
		$text = $updates->getMessage()->getText();
		$command=$text;
		$params="";
		$response_txt="";

    //PERLU DIPERHATIKAN 437329516
		Telegram::sendMessage([
				'chat_id' => 67409805,
				'text' => "DEBUG\n".$updates->isType('callback_query')
				]);

		try{
			    if(isset($updates['callback_query'])){
					$text = $updates['callback_query']['data'];
					$chatid = $updates['callback_query']['message']['chat']['id'];
					$callback_query_id = $updates['callback_query']['id'];
					$responses = Telegram::answerCallbackQuery([
						'callback_query_id' => $callback_query_id,
						'text' => '',
						'show_alert' => false
					]);
				}else{
					$text = $updates['message']['text'];
					$chatid = $updates['message']['chat']['id'];
					$callback_query_id = 0;
				}
			// $callback_query_id = "";
		    // if($updates->isType('callback_query')){
				// Telegram::sendMessage([
				// 'chat_id' => 67409805,
				// 'text' => "Masuk Call Back"
				// ]);
				// // $text = $updates['callback_query']['data'];
				// // $chatid = $updates['callback_query']['message']['chat']['id'];
				// // $callback_query_id = $updates['callback_query']['id'];
				// //$query = $updates->getCallbackQuery();

				// // Telegram::answerCallbackQuery([
					// // 'callback_query_id' => $callback_query_id
				// // ]);
			// }else{
				// $text = $updates['message']['text'];
				// $chatid = $updates['message']['chat']['id'];
				// $callback_query_id = 0;
			// }



			// Telegram::sendMessage([
				// 'chat_id' => 67409805,
				// 'text' => "DEBUG\n".$callback_query_id
				// ]);

			// if (strpos($text," ")>0){
				// $command = substr($text, 0, strpos($text, " "));
				// $params = substr($text, strpos($text, " "), strlen($text)-strpos($text, " "));
			// }

			switch(true)  {
				case $command === '/start':
					$this->showStartWebhook($chatid);
					$response_txt .= "Mengenal command dan berhasil merespon\n";
					break;
				case $command === '/driver':
					$this->showDriverList($chatid);
					$response_txt .= "Mengenal command dan berhasil merespon\n";
					break;
				case $command === '/updatedriver':
					$this->updateDriverFirst($updates, $chatid);
					$response_txt .= "Mengenal command dan berhasil merespon\n";
					break;
				case substr($command,0,7) === '/upddrv':
					$this->updateDriverSecond($command, $chatid);
					$response_txt .= "Mengenal command dan berhasil merespon\n";
					break;
				case substr($command,0,9) === 'update_ya':
					$this->updateDriverThird($command, $chatid);
					$response_txt .= "Mengenal command dan berhasil merespon\n";
					break;
				case $command === '/sensusedc':
					$this->sensusEdc($params, $chatid);
					$response_txt .= "Mengenal command dan berhasil merespon\n";
					break;
				// default:
					// $info = 'I do not understand what you just said. Please choose an option';
					// $category = 'Warning';
					// $this->showGeneralNotif($telegram, $chatid, $info, $category);
					// $response_txt .= "Tidak mengenal command\n";
			}
      //67409805
		}catch(\Exception $e){
			Telegram::sendMessage([
				'chat_id' => 437329516,
				'text' => "ERROR\n".$e->getMessage()
				]);
		}

		//log - pencatatan aktifitas
		date_default_timezone_set('Asia/Bangkok');
		$sql = "insert into log values ('".date('Y-m-d H:i:s')."','$chatid','$text')";
		$response = DB::insert($sql);
		if ($response>0){
			$response_txt .= "Berhasil menyimpan log\n";
		}else{
			$response_txt .= "Gagal menyimpan log\n";
		}

		return 'ok';
	}

	public function showStartWebhook($chatid){
		$message = 'Welcome to BOT BRI JOGJA. This Bot is still under construction, and will be back soon to serve the best';

		$response = Telegram::sendMessage([
			'chat_id' => $chatid,
			'text' => $message
		]);
	}

	public function showVersiBrilinkWebhook($chatid, $params){
		$message="_";
		//menampilkan help bila command tidak disertai parameter
		if ($params ==""){
			$message = "Update Versi Brilink\n\n";
			$message .= "Command untuk mengupdate status update versi dengan format sbb :\n";
			$message .= "/versibrilink A#B#C\n\n";
			$message .= "huruf A diisi dengan :\n";
			$message .= "1. plnh2h \n";
			$message .= "\n";
			$message .= "huruf B diisi dengan tid \n\n";
			$message .= "huruf C diisi dengan status :\n";
			$message .= "1. OK\n";
			$message .= "2. Ditarik\n";
			$message .= "3. Rusak\n";
		}else{
			$parameters = explode("#",$params);
			$tid="";
			$agen="";
			//cek kelengkapan parameter
			if (count($parameters)!=3){
				$message = "Kelengkapan parameter belum sesuai\n";
				goto langsungan;
			}
			//cek parameter kategori
			$result = DB::table('updatebrilink')->where('kategori',trim($parameters[0]))->get();
			if ($result->count()==0){
				$message = "Parameter (1) kategori tidak sesuai dengan yang ada di sistem\n";
				goto langsungan;
			}
			//cek parameter tid
			$result = DB::table('updatebrilink')->where('tid',trim($parameters[1]))->get();
			if ($result->count()==0){
				$message = "Parameter (2) tid di luar daftar yang ada di sistem\n";
				goto langsungan;
			}else{
				if($result[0]->status!=""){
					$message = "TID sudah pernah di-update\n";
					goto langsungan;
				}else{
					$tid = $result[0]->tid;
					$agen = $result[0]->agen;
					$merk = $result[0]->merk;
				}
			}
			//cek parameter status
			$status = strtolower(trim($parameters[2]));
			if ($status != "ok" && $status != "ditarik" && $status != "rusak"){
				$message = "Parameter (3) status di luar daftar (OK, Ditarik, Rusak)\n";
				goto langsungan;
			}
			$response = DB::update('update updatebrilink set status = ? where tid = ?', array($status, trim($parameters[1])));
			$message = "Data berhasil diupdate\n";
			$message .= "TID ".$tid."\n";
			$message .= "Nama ".$agen."\n";
			$message .= "Merk ".$merk."\n";
			$message .= "Status ".strtoupper($status)."\n";
		}

		langsungan :
		$response = Telegram::sendMessage([
			'chat_id' => $chatid,
			'text' => $message
		]);
	}

	public function sampleTIDWebhook($chatid){
		$result = DB::table('updatebrilink')->where('status','')->get();
		$message = "DAFTAR SAMPEL 5 TID YANG BELUM DIUPDATE\n\n";
		$message .= $result[0]->tid."\n";
		$message .= $result[1]->tid."\n";
		$message .= $result[2]->tid."\n";
		$message .= $result[3]->tid."\n";
		$message .= $result[4]->tid."\n";
		// for ($i=0;$i<5;$i++)
			// $message .= $result[$i]->tid."\n";
		// }
		$response = Telegram::sendMessage([
			'chat_id' => $chatid,
			'text' => $message
		]);
	}

	public function showGeneralNotif($telegram, $chatid, $info, $category){
		$message = $category."\n";
		$message .= $info;

		$response = $telegram->sendMessage([
			'chat_id' => $chatid,
			'text' => $message
		]);
	}

	public function showStart($telegram, $chatid){
		$message = 'Welcome to BOT BRI JOGJA. This Bot is still under construction, and will be back soon to serve the best';

		$response = Telegram::sendMessage([
			'chat_id' => $chatid,
			'text' => $message
		]);
	}

	public function showDriverList($chatid){
		$message="";
		$result = DB::table('driver')->get();
		$message = "DAFTAR DRIVER KANWIL".$result->count()." \n\n";
		if ($result->count()>0){
			for ($i=0;$i<$result->count();$i++){
				$message .= $result[$i]->nama."\n";
				$message .= "Status : ".$result[$i]->status."\n";
				if ($result[$i]->status == "Terpakai"){
					$message .= $result[$i]->tanggal_mulai." - ".$result[$i]->tanggal_akhir."\n";
				}
			}
		}

		$response = Telegram::sendMessage([
			'chat_id' => $chatid,
			'text' => $message
		]);
	}

	public function updateDriverFirst($telegram, $chatid){
		$message="";
		$result = DB::table('driver')->get();
		$message = "DAFTAR DRIVER KANWIL".$result->count()." \n\n";
		if ($result->count()>0){
			for ($i=0;$i<$result->count();$i++){
				$message .= $result[$i]->nama."\n";
				$message .= "Status : ".$result[$i]->status."\n";
				if ($result[$i]->status == "Terpakai"){
					$message .= $result[$i]->tanggal_mulai." - ".$result[$i]->tanggal_akhir."\n";
				}
				$message .= "/upddrv_".$result[$i]->Id."\n\n";
			}
		}

		$response = Telegram::sendMessage([
			'chat_id' => $chatid,
			'text' => $message
		]);
	}

	public function updateDriverSecond($command, $chatid){
		$message="";
		$message="Mau dipake? \n";
		$keyboard = Keyboard::make()
					->inline()
					->row(
						Keyboard::inlineButton(['text' => 'Ya', 'callback_data' => 'update_ya '.$command]),
						Keyboard::inlineButton(['text' => 'Tidak', 'callback_data' => 'update_no '.$command])
					);

		$response = Telegram::sendMessage([
		  'chat_id' => $chatid,
		  'text' => $message,
		  'reply_markup' => $keyboard
		]);

		$messageId = $response->getMessageId();
	}

	public function updateDriverThird($command, $chatid){
		$message="";
		$message="Data terupdate ".$command."\n";
		$response = Telegram::sendMessage([
		  'chat_id' => $chatid,
		  'text' => $message,
		  'reply_markup' => $keyboard
		]);

		$messageId = $response->getMessageId();
	}

	public function sensusEdc($param, $chatid){
		$params = explode("#",$param);

		if ($param ==""){
			$message = "Sensus EDC\n\n";
			$message .= "Cara melakukan sensus EDC :\n";
			$message .= "/sensusedc A#B#C#D\n\n";
			$message .= "huruf A diisi dengan TID :\n";
			$message .= "\n";
			$message .= "huruf B diisi dengan SN EDC \n\n";
			$message .= "huruf C diisi dengan Nomor Hape Simcard (08xx) \n";
			$message .= "huruf D diisi dengan merk :\n";
			goto langsungan;
		}else{
			if(count($params)<4){
				$message = "Kelengkapan parameter belum sesuai\n";
				goto langsungan;
			}

			$result = DB::table('sensusedc')->where('tid',trim($params[0]))->get();
			if ($result->count()!=0){
				$message = "TID sudah pernah di-insert\n";
				goto langsungan;
			}
		}

		$result = DB::table('sensusedc')->insert(['tid'=>$params[0],'sn'=>$params[ 1],'simcard'=>$params[2],'merk'=>$params[3]]);
		$message = "Data berhasil terinput\n";
		$message .= "TID ".$params[0]."\n";
		$message .= "SN EDC ".strtoupper($params[1])."\n";
		$message .= "Simcard ".strtoupper($params[2])."\n";
		$message .= "Merk ".strtoupper($params[3])."\n";

		langsungan :
		$response = Telegram::sendMessage([
			'chat_id' => $chatid,
			'text' => $message
		]);
	}
}
