<!-- <?php

// namespace App\Console\Commands;
//
// use Illuminate\Console\Command;
// use Telegram\Bot\Api;
// use Illuminate\Support\Facades\DB;

// class respondTelegram extends Command
// {
//     /**
//      * The name and signature of the console command.
//      *
//      * @var string
//      */
//     protected $signature = 'telegram:update';
//
//     /**
//      * The console command description.
//      *
//      * @var string
//      */
//     protected $description = 'Update BRI Jogja Bot and respond it by using getUpdates methode';
//
//     /**
//      * Create a new command instance.
//      *
//      * @return void
//      */
//     public function __construct()
//     {
//         parent::__construct();
//     }
//
//     /**
//      * Execute the console command.
//      *
//      * @return mixed
//      */
//     public function handle()
//     {
// 		$updateid="";
// 		$updateid_lama="";
// 		while(true){
// 			// $telegram = new Api(env('TELEGRAM_BOT_TOKEN'));
// 			$response = $telegram->getUpdates();
// 			$request = collect(end($response)); // fetch the last request from the collection
//
// 			$chatid = $request['message']['chat']['id']; // get chatid from request
// 			$text = $request['message']['text']; // get the user sent text
// 			$updateid = $request['update_id'];
// 			$command = $text;
// 			$params="";
// 			$response_txt="";
// 			if($updateid != $updateid_lama){
// 				$updateid_lama=$updateid;
// 				date_default_timezone_set('Asia/Bangkok');
// 				$response_txt .= "[".date('Y-m-d H:i:s')."] \nPesan masuk ID ".$chatid."-".$updateid."  : ".$text."\n";
// 			}else{
// 				//jika pesan sudah pernah diproses, skip
// 				goto lanjut;
// 			}
// 			if (strpos($text," ")>0){
// 				$command = substr($text, 0, strpos($text, " "));
// 				$params = substr($text, strpos($text, " "), strlen($text)-strpos($text, " "));
// 			}
//
// 			switch($command) {
// 				case '/start':
// 					$this->showStart($telegram, $chatid);
// 					$response_txt .= "Mengenal command dan berhasil merespon\n";
// 					break;
// 				case '/sendparam':
// 					$this->showParamsRespond($telegram, $chatid, $params);
// 					$response_txt .= "Mengenal command dan berhasil merespon\n";
// 					break;
// 				case '/versibrilink':
// 					$this->showVersiBrilink($telegram, $chatid, $params);
// 					$response_txt .= "Mengenal command dan berhasil merespon\n";
// 					break;
// 				default:
// 					$info = 'I do not understand what you just said. Please choose an option';
// 					$category = 'Warning';
// 					$this->showGeneralNotif($telegram, $chatid, $info, $category);
// 					$response_txt .= "Tidak mengenal command\n";
// 			}
//
// 			//log - pencatatan aktifitas
// 			$sql = "insert into log values ('".date('Y-m-d H:i:s')."','$chatid','$text')";
// 			$response = DB::insert($sql);
// 			if ($response>0){
// 				$response_txt .= "Berhasil menyimpan log\n";
// 			}else{
// 				$response_txt .= "Gagal menyimpan log\n";
// 			}
// 			if($response_txt!=""){
// 				echo $response_txt."\n";
// 			}
// 			lanjut :
// 			sleep(2);
// 		}
//
// 		return $response_txt;
//     }
//
//
// 	public function showGeneralNotif($telegram, $chatid, $info, $category){
// 		$message = $category."\n";
// 		$message .= $info;
//
// 		$response = $telegram->sendMessage([
// 			'chat_id' => $chatid,
// 			'text' => $message
// 		]);
// 	}
//
// 	public function showStart($telegram, $chatid){
// 		$message = 'Welcome to BOT BRI JOGJA. This Bot is still under construction, and will be back soon to serve the best';
//
// 		$response = $telegram->sendMessage([
// 			'chat_id' => $chatid,
// 			'text' => $message
// 		]);
// 	}
//
// 	public function showParamsRespond($telegram, $chatid, $params){
// 		$message = 'Anda mengirimkan parameter : '.$params."\n";
// 		$message .= 'Bener kan?!';
// 		$messages = explode("#",$params);
// 		$message .= "Param 1 :".$messages[0].", Param 2 :".$messages[1]."\n";
// 		$response = $telegram->sendMessage([
// 			'chat_id' => $chatid,
// 			'text' => $message
// 		]);
// 	}
//
// 	public function showVersiBrilink ($telegram, $chatid, $params){
// 		$message="_";
// 		//menampilkan help bila command tidak disertai parameter
// 		if ($params ==""){
// 			$message = "Update Versi Brilink\n\n";
// 			$message .= "Command untuk mengupdate status update versi dengan format sbb :\n";
// 			$message .= "/versibrilink A#B#C\n\n";
// 			$message .= "huruf A diisi dengan :\n";
// 			$message .= "1. plnh2h \n";
// 			$message .= "\n";
// 			$message .= "huruf B diisi dengan tid \n\n";
// 			$message .= "huruf C diisi dengan status :\n";
// 			$message .= "1. OK\n";
// 			$message .= "2. Ditarik\n";
// 			$message .= "3. Rusak\n";
// 		}else{
// 			$parameters = explode("#",$params);
// 			$tid="";
// 			$agen="";
// 			//cek kelengkapan parameter
// 			if (count($parameters)!=3){
// 				$message = "Kelengkapan parameter belum sesuai\n";
// 				goto langsungan;
// 			}
// 			//cek parameter kategori
// 			$result = DB::table('updatebrilink')->where('kategori',trim($parameters[0]))->get();
// 			if ($result->count()==0){
// 				$message = "Parameter (1) kategori tidak sesuai dengan yang ada di sistem\n";
// 				goto langsungan;
// 			}
// 			//cek parameter tid
// 			$result = DB::table('updatebrilink')->where('tid',trim($parameters[1]))->get();
// 			if ($result->count()==0){
// 				$message = "Parameter (2) tid di luar daftar yang ada di sistem\n";
// 				goto langsungan;
// 			}else{
// 				if($result[0]->status!=""){
// 					$message = "TID sudah pernah di-update\n";
// 					goto langsungan;
// 				}else{
// 					$tid = $result[0]->tid;
// 					$agen = $result[0]->agen;
// 				}
// 			}
// 			//cek parameter status
// 			$status = strtolower(trim($parameters[2]));
// 			if ($status != "ok" && $status != "ditarik" && $status != "rusak"){
// 				$message = "Parameter (3) status di luar daftar (OK, Ditarik, Rusak)\n";
// 				goto langsungan;
// 			}
// 			$response = DB::update('update updatebrilink set status = ? where tid = ?', array($status, trim($parameters[1])));
// 			$message = "Data berhasil diupdate\n";
// 			$message .= "TID ".$tid."\n";
// 			$message .= "Nama ".$agen."\n";
// 			$message .= "Status ".strtoupper($status)."\n";
// 		}
//
// 		langsungan :
// 		$response = $telegram->sendMessage([
// 			'chat_id' => $chatid,
// 			'text' => $message
// 		]);
// 	}
//
// 	public function fire()
// 	{
// 		$this->info("Telegram BRI Jogja Bot updated and responded");
// 	}
// }
