<?php
namespace App\Http\Controllers;

use Illuminate\Support\Facades\DB;

class ShareLoct extends Controller
{//awal kelas
  public function webhook()
	{
	try{//awal try
		$request = Telegram::getWebhookUpdates();
		$month_input = date("Y-m");
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
        // $firstname=$request->getMessage()->getChat()->getFirst_Name();
        // $number=$request->getMessage()->getChat()->getPhone_Number();
				$callback_query_id = 0;
			}//end else

			switch($text)
			{//mulai switch
					case $text === '/share':
						$this->shareLoct($chatid);
          break;
				// default:
				// 	 $this->defaultMessage($chatid, $text, $username, $messageid);
				// 	 break;
			}//end switch
		}catch (\Exception $e) {
			Telegram::sendMessage([
				'chat_id' => 437329516,
				'text' => "Reply ".$e->getMessage()
			]);
		}//end catch
	}//akhir fungsi webhook

  public function shareLoct($chatid)
  {//awal fungsi share loct
    $message="";
    $message="How we can contact you?";
    $inlineLayout = [[
			Keyboard::inlineButton(['text' => 'My Phone Number', 'callback_data' => 'number']),
			Keyboard::inlineButton(['text' => 'CANCEL', 'callback_data' => 'cancel'])
		]];
    $reply_markup = Telegram::replyKeyboardMarkup([
      'resize_keyboard' => true,
      'one_time_keyboard' => true,
      'inline_keyboard' => $inlineLayout
    ]);
    $response = Telegram::sendMessage([
      'chat_id' => $chatid,
      'parse_mode' => 'markdown',
      'text' => $message,
      'reply_markup' => $reply_markup
    ]);

//     bot.onText(/^\/place_order/, function (msg, match) {
//     var option = {
//         "parse_mode": "Markdown",
//         "reply_markup": {
//             "one_time_keyboard": true,
//             "keyboard": [[{
//                 text: "My phone number",
//                 request_contact: true
//             }], ["Cancel"]]
//         }
//     };
//     bot.sendMessage(msg.chat.id, "How can we contact you?", option).then(() => {
//         bot.once("contact",(msg)=>{
//             var option = {
//                 "parse_mode": "Markdown",
//                 "reply_markup": {
//                     "one_time_keyboard": true,
//                     "keyboard": [[{
//                         text: "My location",
//                         request_location: true
//                     }], ["Cancel"]]
//                 }
//             };
//             bot.sendMessage(msg.chat.id,
//                             util.format('Thank you %s with phone %s! And where are you?', msg.contact.first_name, msg.contact.phone_number),
//                             option)
//             .then(() => {
//                 bot.once("location",(msg)=>{
//                     bot.sendMessage(msg.chat.id, "We will deliver your order to " + [msg.location.longitude,msg.location.latitude].join(";"));
//                 })
//             })
//         })
//     })
//
// });
}//akhir fungsi shareLoct

  // public function number($chatid, $firstname)
  // {
  //   $longitude='longitude';
  //   $latitude='latitude';
  //   $message="Thank you .$firstname., with phone number .$number. , Where are you?"
  //   $response = Telegram::sendMessage([
  //     'chat_id' => $chatid,
  //     'parse_mode' => 'markdown',
  //     'text' => $message,
  //     'longitude'=>$longitude,
  //     'latitude'=>$latitude,
  //     'reply_markup' => $reply_markup
  //   ]);
  // }



}//akhir kelas

?>
