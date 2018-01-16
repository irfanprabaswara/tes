<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
// use Spatie\Browsershot\Browsershot;
use JonnyW\PhantomJs\Client;

class BrowsershotController extends Controller
{
    //
	public function capturePage(){
		$client = Client::getInstance();
		$client->getEngine()->setPath('C:/xampp/htdocs/kanwilbot/bin/phantomjs.exe');

		$page="https://www.google.co.id/?gws_rd=cr&dcr=0&ei=u2IuWqzEI4jevgTFkrOQCQ";

		$request  = $client->getMessageFactory()->createCaptureRequest($page);
		$response = $client->getMessageFactory()->createResponse();

		$file = 'D:/screenshots/file.jpg';

		$request->setOutputFile($file);

		$client->send($request, $response);

		echo $response->getStatus();
		echo $response->getURL()."<br>";
		echo $response->getContent()."<br>";
		var_dump($response->getHeaders());
	}
}
