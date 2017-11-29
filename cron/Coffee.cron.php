<?php

namespace Cron;

class Coffee extends \Cron\Cron{

	public $crontab = "0 * * * 1-5";

    private $channel = "C8471J5QS";
    private $id;

	public function run(){

        if ( date("G") < 9 || date("G") > 16 ){
			return;
		}

        $members = $this->getMembers();
        $this->id = md5(time());

		$sql = "INSERT INTO coffee_requests SET token = ?";

		$insertStatement = $this->database->prepare($sql);
		$insertStatement->execute(array($this->id));

        foreach( $members as $member ){
            $this->sendPost($member);
        }
	}

    public function getChannels(){

		$response = $this->kernel->get("channels.list", array(
			"channel" => $this->channel
		));

		return $response;
    }

    public function getMembers(){

		$response = $this->kernel->get("conversations.members", array(
			"channel" => $this->channel
		));


        return $response->members;
    }

    public function getUser($user){

		$response = $this->kernel->get("users.info", array(
			"user" => $user
		));

		return $response;

    }

    public function sendPost($userid) {

		$options = array(
			array(
				"text" => "Zwarte koffie",
				"value" => "black_coffee"
			),
			array(
				"text" => "Cappuccino",
				"value" => "cappuccino"
			),
			array(
				"text" => "Chocolademelk",
				"value" => "choco"
			),
			array(
				"text" => "Thee",
				"value" => "tea"
			),
			array(
				"text" => "Wiener melange",
				"value" => "wiener_melange"
			),
			array(
				"text" => "Espresso",
				"value" => "espresso"
			),
			array(
				"text" => "Dubbele espresso",
				"value" => "double_espresso"
			)
		);

		if ( date("N") == 5 ){
			$options[] = array(
				"text" => "Pitt bier",
				"value" => "bitt_beer"
			);

			$options[] = array(
				"text" => "Hertog jan",
				"value" => "hertog_jan"
			);
		}

        $data = array(
            "channel" => $this->channel,
            "user" => $userid,
			"text" => ":coffee: Wil je ook een bakje koffie of thee? Over 5 minuten wordt er voor je gehaald",
            "attachments" => json_encode(array(
				array(
					"text" => "Kies hieronder je favoriete warme drank",
					"callback_id" => $this->id,
					"color" => "#3AA3E3",
					"attachment_type" => "default",
					"actions" => array(array(
                        "name" => "coffee",
                        "text" => "Kies je drankje?",
                        "type" => "select",
                        "options" => $options
					)),
					"token" => $this->kernel->token,
					"response_url" => "http://slack.michel-heitbrink.nl/hooks/?hook=Coffee"
				)
			))
        );

		$response = $this->kernel->get("chat.postEphemeral", $data);
    }
}

?>