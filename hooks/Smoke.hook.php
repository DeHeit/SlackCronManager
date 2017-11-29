<?php

namespace Hook

class Smoke extends \Hook\Hook{

	private $username = "Chesterfield";
	private $icon = "http://michel-heitbrink.nl/slack/img/snoop.jpg";

	public function get($post){

	    $content = file_get_contents("http://gpsgadget.buienradar.nl/data/raintext/?lat=53.2&lon=5.8");

        $lines = explode("\n", $content);

        if ( count($lines) > 1 ){
            $now = $lines[0];
            $next = $lines[1];

            $statusNow = intval(substr($now, 0, 3));
            $statusNext = intval(substr($next, 0, 3));

            if ( $statusNow <= 10  &&  $statusNext <= 10 ){
				$payload = array(
					"channel" => "#" . $post["channel_name"],
					"text" => "Je kan. Het is minimaal 5 minuten droog. Neem je buddies mee.",
					"username" => $this->username,
					"icon_url" => $this->icon
				);
            }
            else {
				$time = $this->getDryTime($lines);

				if ( $time ){
					$payload = array(
						"channel" => "#" . $post["channel_name"],
						"text" => "Je kan beter om {$time}, {$post["user_name"]}.",
						"username" => $this->username,
						"icon_url" => $this->icon
					);
				}
				else {

					$payload = array(
						"channel" => "#" . $post["channel_name"],
						"text" => "Helaas, dat wordt roken in het rokershok, {$post["user_name"]}.",
						"username" => $this->username,
						"icon_url" => $this->icon
					);
				}

            }

			$this->kernel->post(json_encode($payload));
			return;
        }
	}

    public function getDryTime($lines) {

		array_shift($lines);

		foreach( $lines as $line ){

			$mil = intval(substr($line, 0, 3));

			if ( $mil < 10 ){
				return substr($line, 4, 5);
			}
		}

		return false;
	}
}
?>