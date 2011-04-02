<?

//TODO event loop that always checks which functions are registered...
// loops through $history var checking for registered keywords to call func with...

// :$keyword $arg // user defined keyword
// :: $arg //built-in global...so ":: weather" would give you weather
// or maybe just :bot weather


class HipChatBot {

	const BASE_API_URL = 'https://api.hipchat.com';
	const API_VERSION = 'v1';

	protected $api_key;
	protected $room_id;
	protected $new_history;
	protected $seen_history = array();

	// protected $registered_keywords

	public function __construct($api_key,$room_id) {
		$this->api_key = $api_key;
		$this->room_id = $room_id;;

		//echo print_r($this->_check_history('asdf'));

		// register the default ::weather function...
		$this->_update_history();
		$this->parse_history('bot', function($args) {
			if ( array_shift($args) == 'weather' ) {
				return 'yes';
			}
			return 'no';
		});
	}

	public function register_command($keyword,$display_name,$method) {
		// store the this info in array...
	}

	public function parse_history($keyword,$func) { // look for keyword and args
		$pattern = '/\:' . $keyword . '(.+)$/';
		foreach ( $this->new_history as $h ) {

			preg_match_all($pattern,$h['message'],$matches);
			foreach ( $matches[1] as $match ) {
				$args = explode(' ',trim($match));
				echo 'DOES THIS WORK?: ' . call_user_func($func,$args);
			}
		}
	}
	
	public function _update_history() {
		$history = $this->_get_history();
		$history = $history['messages'];
		$this->new_history = array();
		foreach ( $history as $h ) {
			if ( !in_array($h,$this->seen_history) ) {
				$this->new_history[] = $h;
			}
		}
		$this->seen_history = $history;
	}

	protected function _get_history() {
		$request = self::BASE_API_URL . '/' . self::API_VERSION . '/rooms/history?room_id=' . $this->room_id  . '&date=recent&timezone=PST&format=json&auth_token=' . $this->api_key;

		echo "\n" . $request . "\n";
		$curl = curl_init();
		curl_setopt($curl, CURLOPT_URL, $request);
		curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($curl, CURLOPT_FOLLOWLOCATION, true);
		$r = curl_exec($curl);
		self::d($r);
		curl_close($curl);

		$data = json_decode($r,true);
		return $data;
	}

	public static function d($s) {
		echo "\n\n{$s}\n\n";
	}

}

$hcb = new HipChatBot('92b4cb825cef189def9a7b91b55a79','16110');

//$hcb->register_bot()

// $hcb->run(); // goes into event loop

// $arg should/can be an of the explode(' ') after keyword...
/* function ($arg) { */
/* 	// do stuff with $arg.. */
	// return what should be printed...
/* } */