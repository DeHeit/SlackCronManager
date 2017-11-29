<?php
namespace Hook;

class Coffee extends \Hook\Hook{

	public function get($post){
        
		$payload = json_decode($post["payload"]);

		$sql = "SELECT * FROM coffee_response WHERE username = ? AND token = ?";

        $statement = $this->database->prepare($sql);
		$statement->execute(array($payload->user->name, $payload->callback_id));

		if ( $statement->rowCount() == 0 ){

			if ( $payload->actions[0]->selected_options[0]->value != "nee" ){

				$sql = "INSERT INTO coffee_response SET username = ?, token = ?, value = ?";

				$insertStatement = $this->database->prepare($sql);
				$insertStatement->execute(array($payload->user->id, $payload->callback_id, $payload->actions[0]->selected_options[0]->value));

				$pload = json_encode(array(
					"response_type" => "ephemeral",
					"replace_original" => true,
					"text" => ":white_check_mark: Je bestelling is genoteerd"
				));

				$ch = curl_init();

				curl_setopt($ch, CURLOPT_URL, $payload->response_url);
				curl_setopt($ch, CURLOPT_POST, 1);
				curl_setopt($ch, CURLOPT_POSTFIELDS, "payload={$pload}");
				curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

			  	$response = curl_exec($ch);
		        $response = json_decode($response);

			  	curl_close($ch);
			}
		}
	}
}

?>
