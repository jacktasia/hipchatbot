<?php

class HipChatBot {

	const SLEEP_TIME = 15;
	const TIMEZONE = 'PST';
	const BASE_API_URL = 'https://api.hipchat.com';
	const API_VERSION = 'v1';

	protected $api_key;
	protected $room_id;
	protected $server_on = true;
	protected $new_history;
	protected $seen_history = array();
	protected $registered_bots = array();
	protected $hit_count = 0;

	public function __construct($api_key,$room_id) {
		$this->api_key = $api_key;
		$this->room_id = $room_id;;
		$this->_update_history();
		$this->register_bot('kill-bot','Killer', function($args) {
			HipChatBot::debug('Got killed.');
			die("\nBye.\n");
		});
	}

	public function run() {
		while ( $this->server_on ) {
			$this->_update_history();
			foreach ( $this->registered_bots as $f ) {
				$this->parse_history($f['keyword'],$f['display_name'],$f['func']);
			}
			self::debug("sleeping for " . self::SLEEP_TIME . ' seconds | server hits: ' . ++$this->hit_count);
			sleep(self::SLEEP_TIME);
		}
	}

	public function register_bot($keyword,$display_name,$func) {
		$arr = array('keyword' => $keyword,
					 'display_name' => $display_name,
					 'func' => $func);
		$this->registered_bots[] = $arr;
	}

	public function parse_history($keyword,$display_name,$func) {
		$pattern = '/\:' . $keyword . '(.*)$/';
		foreach ( $this->new_history as $h ) {

			preg_match_all($pattern,$h['message'],$matches);
			foreach ( $matches[1] as $match ) {
				$args = explode(' ',trim($match));
				if ( !is_array($args) ) {
					$args = array($args);
				}
				$response = $func($args);
				$this->_send_message($response, $display_name);
			}
		}
	}
	
	public function _update_history() {
		$history = $this->_get_history();
		$history = $history['messages'];
		$this->new_history = array();
		$count = 0;
		foreach ( $history as $h ) {
			if ( !in_array($h,$this->seen_history,true) ) {
				$this->new_history[] = $h;
				$count++;
			}
		}
		self::debug('updated, new items:' . $count);
		$this->seen_history = $history;
	}

	public function generate_url($type,$params=null) {
		$url = self::BASE_API_URL . '/' . self::API_VERSION;
		$url .= '/rooms/' . $type . '?';
		$core_params = array('room_id' => $this->room_id,
							'auth_token' =>  $this->api_key,
							'timezone' => self::TIMEZONE);
		if ( is_array($params) ) {
			$core_params = array_merge($core_params,$params);
		}
		return $url . http_build_query($core_params);
	}

	protected function _get_history() {
		$request = $this->generate_url('history',array('date'=>'recent'));
		$response = self::_curl($request);
		$data = json_decode($response,true);
		return $data;
	}

	public function _send_message($message,$from) {
		$params = array('message'=> $message,
						'from' => $from);
		$params['message'] = str_replace(array('<BR />','<br/>','<BR/>'),
										array('<br />','<br />','<br />'),$params['message']);
		self::debug("Sent Message:\n\t\t" . $params['message']);
		$request = $this->generate_url('message',$params);
		self::_curl($request);
	}

	public static function _curl($url) {
		$curl = curl_init();
		curl_setopt($curl, CURLOPT_URL, $url);
		curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($curl, CURLOPT_FOLLOWLOCATION, true);
		$response = curl_exec($curl);
		curl_close($curl);
		return $response;
	}

	public static function debug($s) {
		echo "\n" . date('Y-m-d h:m:s') . " => {$s}\n";
	}

}
