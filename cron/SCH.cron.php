<?php

namespace Cron;
/**
  * Posts Tourupdates on slack
  * @author Michel Heitbrink
  */
class SCH extends \Cron\Cron{

	public $crontab = "*/5 * * * *";

    protected $channel = "#scheerenveen";

    protected $feeds = array(
        "voetbalprimeur" => array(
            "url" => "http://www.voetbalprimeur.nl/rss/?tag=sc-heerenveen",
            "encoding" => "utf-8",
            "name" => "Voetbal Primeur"
        ),
        "feanonline" => array(
            "url" => "http://www.feanonline.nl/rss/rss.xml",
            "encoding" => "utf-8",
            "name" => "Feanonline"
        ),
        "scheerenveen" => array(
            "url" => "https://www.sc-heerenveen.nl/rss/scheerenveennieuws",
            "encoding" => "utf-8",
            "name" => "SC Heerenveen"
        )
    );

	public function run(){
        $this->checkNewArticles();
        $this->postNewArticles();
	}

    public function checkNewArticles(){

        foreach( $this->feeds as $page=>$feed ){

            $xml = file_get_contents($feed["url"]);

            if ( strtolower($feed["encoding"]) == "iso-8859-1"){
                $xml = iconv('UTF-8', 'ISO-8859-1', $xml);
            }

            $xml = simplexml_load_string($xml);
            $items = $xml->xpath('//item');

            foreach( $items as  $item ){

                $title = strip_tags(html_entity_decode( (string) $item->title));
                $description = ( isset($item->description) ) ?  strip_tags(html_entity_decode( (string) $item->title)) : null;
                $link = (string) $item->link;

                $sql = "SELECT * FROM articles WHERE site = ? AND (title = ? OR link = ?)";

                $statement = $this->database->prepare($sql);

                if (  $statement->execute(array($page, $title, $link)) ){

                    if ( $statement->rowCount() == 0 ){

						if ( strpos(strtolower($title), "esporter") === false ){
							$sql = "INSERT INTO articles SET site = ?, title = ?, description = ?, link = ?";

							$insertStatement = $this->database->prepare($sql);
							$insertStatement->execute(array($page, $title,$description, $link));
						}
                    }
                }

            }
        }
    }

    public function postNewArticles(){

        $sql = "SELECT * FROM articles WHERE posted = 0 ORDER BY RAND() LIMIT 1";

        $statement = $this->database->prepare($sql);

        if (  $statement->execute() ){

            if ( $statement->rowCount() == 1 ){

                if ( $row = $statement->fetchObject() ){

                    $name = $this->feeds[$row->site]["name"];

                    $text = "*" . $row->title . "*";

                    if ( isset($row->description) && !empty($row->description)){
                        $text .= "\n" .$row->description;
                    }

                    $text .= "\n" . $row->link;


                    $payload = array(
    					"channel" => $this->channel,
    					"text" => $text,
    					"username" => $name,
    					"icon_url" => "http://slack.michel-heitbrink.nl/img/rss/{$row->site}.jpg"
    				);

    				$this->kernel->post(json_encode($payload));

                    $sql = "UPDATE articles SET posted = 1 WHERE id = ?";

                    $updateStatement = $this->database->prepare($sql);
                    $updateStatement->execute(array($row->id));

                }
            }
        }

    }
}
