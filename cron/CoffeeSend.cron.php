<?php
namespace Cron;

class CoffeeSend extends \Cron\Cron{

	public $crontab = "5 * * * 1-5";
    private $channel = "C8471J5QS";

	public function run(){

		$responders = array();

		$sql = "SELECT * FROM coffee_response WHERE token = (SELECT token FROM coffee_requests WHERE processed = 0 ORDER BY date_entered DESC LIMIT 1)";

		$st = $this->database->prepare($sql);
		$st->execute();

		$list = "*Dit is de bestelling:*\n";
		$order = array();

		while ( $response = $st->fetchObject() ){

			$user = $this->getUser($response->username);
			$responders[$response->username] = $user;

			if ( !isset($order[$response->value]) ){
				$order[$response->value] = 0;
			}

			$order[$response->value]++;

			$list .= "*{$user}* wil een *" . $this->getDutchName($response->value) . "*\n";
		}

		$orderlist = "";

		foreach ( $order as $k => $o ){
			$orderlist .= $this->getDutchName($k) . " *" . $o . "*,";
		}

		$orderlist = rtrim($orderlist, ",");

		if ( count($responders) > 0 ){
			
			//Remove Erwin
			if ( isset($responders["U6YLQ3KM4"]) ){
				unset($responders["U6YLQ3KM4"]);
			}

			if ( count($responders) > 0 ){
				
				//Possible to give weight to people to make it unfair. By default everybody has the same change.
				$chancelist = array();

				foreach( $responders as $uid => $name ){

					for( $i = 0; $i < 1; $i++ ){
						$chancelist[] = $uid;
					}
				}

				$picked = array_rand($chancelist, 1);

				$target = $responders[$chancelist[$picked]];

				$this->postMessage("*{$target}*, ga eens als de wiedeweerga koffie halen:rocket:\n\n{$list}\n{$orderlist}");

			}
			else {
				$this->postMessage("{$responders["U6YLQ3KM4"]} heeft als enige besteld. Wie wil het voor hem halen?:rocket:\n\n{$list}\n{$orderlist}");
			}
		}

		//Update request token
		$sql = "UPDATE coffee_requests SET processed = 1 WHERE processed = 0";

		$st = $this->database->prepare($sql);
		$st->execute();
	}

	public function postMessage($text){

		$data = array(
            "channel" => $this->channel,
			"text" => $text,
			"username" => $this->username,
			"icon_url" => $this->icon,
    		"mrkdwn" => true
        );

		$this->kernel->get("chat.postMessage", $data);
	}

	public function getUser($user){

		$response = $this->kernel->get("users.info", array(
			"user" => $user
		));

		if ( $response->ok ){
			return $response->user->profile->real_name;
		}
	}

	public function getDutchName($value){
		switch($value){
			case "black_coffee":
				return "zwarte koffie";
			case "cappuccino":
				return "cappuccino";
			case "choco":
				return "chocolademelk";
			case "tea":
				return "thee";
			case "wiener_melange":
				return "wiener melange";
			case "espresso":
				return "wspresso";
			case "double_espresso":
				return "dubbele espresso";
			case "pitt_bier":
				return "pitt bier";
			case "hertog_jan":
				return "hertog jan";
		}
	}
}

?>
