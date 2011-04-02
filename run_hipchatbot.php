<?php

require(dirname(__FILE__) . '/class.HipChatBot.php');


$hcb = new HipChatBot('92b4cb825cef189def9a7b91b55a79','16110');

$hcb->register_func('bot','Bot',function($args) {
	$weather_url = 'http://query.yahooapis.com/v1/public/yql?format=json&q=select%20*%20from%20weather.forecast%20where%20location=94111';
	if ( array_shift($args) == 'weather' ) {
		$response = json_decode(HipChatBot::_curl($weather_url),true);
		return $response['query']['results']['channel']['item']['description'];
	}
	return "(taft) don't know what you want!";
});

$hcb->run();
