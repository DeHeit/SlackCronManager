<?php

namespace Kernel;

class Kernel {

    public $token;
    public $posturl;

    public $database;

    private $crons;
    private $hooks;

    private $webroot;

    public $messages = array();

    public function __construct(){

        if( defined("TOKEN") ){
            $this->token = TOKEN;
        }

        if( defined("POSTURL") ){
            $this->posturl = POSTURL;
        }

        if( defined("CRONS") ){
            $this->crons = unserialize(CRONS);
        }

        if( defined("HOOKS") ){
            $this->hooks = unserialize(HOOKS);
        }

        $this->webroot = rtrim($_SERVER["DOCUMENT_ROOT"], "/");
        $this->setDatabase();
    }

    /**
     * Set PDO object as database based on DTABASE defines.
     */
    private function setDatabase(){

        if( defined("DATABASE_NAME") && defined("DATABASE_HOST") && defined("DATABASE_NAME") && defined("DATABASE_PASSWORD") ){

            $this->database = new \PDO("mysql:host=" . DATABASE_HOST . ";dbname=" . DATABASE_NAME . ";charset=utf8", DATABASE_USER, DATABASE_PASSWORD);

        }
    }

    public function get( $method, $data = array()){

        if ( !isset($this->token) ){
            throw new \Exception("TOKEN not configurated");
        }

        if ( !is_array($data) ){
            $data = array();
        }

        $data["token"] = $this->token;

        $querystring = http_build_query($data);

        $url = "https://slack.com/api/{$method}?{$querystring}";

        $ch = curl_init();

		curl_setopt($ch, CURLOPT_URL, $url);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

	  	$response = curl_exec($ch);

        curl_close($ch);

        return  json_decode($response);
    }

    public function post($payload){

        if ( defined("POSTURL") ){

            $payload = ( is_array($payload) ) ? json_encode($payload) : $payload;

    		$ch = curl_init();

    		curl_setopt($ch, CURLOPT_URL, POSTURL);
    		curl_setopt($ch, CURLOPT_POST, 1);
    		curl_setopt($ch, CURLOPT_POSTFIELDS, "payload={$payload}");
    		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

    	  	$response = curl_exec($ch);

    	  	curl_close($ch);

            return $response;
        }
        else {
            throw new \Exception("POSTURL not configurated");
        }
    }

    public function processCrons(){

        if ( isset($_REQUEST["cron"]) ){

            $this->processCron($_REQUEST["cron"], true);

        }
        else {

            foreach($this->crons as $cron){

                $this->processCron($cron);

            }
        }
    }

    public function processCron($cron, $force = false){

        if( is_file($this->webroot . "/cron/" . $cron . ".cron.php") ){

            require_once($this->webroot . "/cron/" . $cron . ".cron.php");

            $cron = "\\Cron\\{$cron}";

            if( class_exists($cron) ){

                $class = new $cron($this);

                if( is_subclass_of($class, '\\Cron\\Cron') ){

                    if( isset($class->crontab) ){

                        $cronFactory = \Cron\CronExpression::factory($class->crontab);

                        if( $cronFactory->isDue() || $force){

                            $class->run();
                            $this->messages[] = "{$cron} " . date("Y-m-d H:i:s") . ": runned\n";

                        }
                        else {

                            $this->messages[] = "{$cron} " . date("Y-m-d H:i:s") . ": idle\n";

                        }
                    }
                    else{

                        throw new \Exception($cron . " as no crontab");

                    }
                }
            }
        }
        else{

            throw new \Exception($cron . " not found");

        }
    }

    /**
     * Process hooks based on request.
     * If hook is set in $_REQUEST, it will be used as a hook.
     * If triggerword is set in $_REQUEST, the corresponding hook will be used.
     * Hooks are defined HOOKS.
     *
     */
    public function processHooks(){

        if ( isset($_REQUEST["hook"]) ){

            $this->processHook($_REQUEST["hook"]);

        }
        else if ( isset($_REQUEST["trigger_word"]) ) {

            $triggerword = strtolower($_REQUEST["trigger_word"]);

        	if ( isset($hooks[$triggerword]) ){

        		$hook = $hooks[$triggerword];

                $this->processHook($hook);
            }
        }
    }

    /**
     * Process single hook
     * @param  String $hook name of the hookclass
     */
    private function processHook($hook){

        if( is_file($this->webroot . "/hooks/" . $hook . ".hook.php") ){

			require_once($this->webroot . "/hooks/" . $hook . ".hook.php");

            $hook = "\\Hook\\{$hook}";

			if( class_exists($hook) ){

				$class = new $hook($this);

				if( is_subclass_of($class, '\\Hook\\Hook') ){
                    
					if ( $text = $class->get($_REQUEST) ){

						if ( is_string($text) ){

							echo json_encode(array("text" => $text)); exit;

						}
					}
                    else {
                        $message[] = "{$hook} " . date("Y-m-d H:i:s") . ": processed\n";
                    }
				}
			}
			else{

                throw new \Exception($hook . " doesn't exists");

			}
		}
        else {
            throw new \Exception($hook . " not found");
        }

    }
}

?>