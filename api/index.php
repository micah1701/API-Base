<?php
/**
 * EXAMPLE IMPLENTATION OF APIBASE
 * 
 */

include_once 'apibase.class.php';

// optionaly wrap apibase with a custom set of rules
class myAPI extends apibase {
    
    public $action = false;
    
    public function __construct()
    {
        // call the base level's __construct()
        parent::__construct();
       
        // require request to include an "action" parameter
        $allowedActions = array("action1","action2","etc");
        
        if(isset($_POST['payload']['action']) && in_array($_POST['payload']['action'],$allowedActions))
        {
            $this->action = $_POST['payload']['action'];
        }
        else
        {
            $this->reply(array("ERROR"=>"Invalid or missing action"),"400 Bad Request");
        }
       
	} 
    
}

$api = new myAPI;

switch ($api->action) 
{
    case ("action1"):
        $api->reply("You requested action 1");
    break;
    
    case ("action2"):
        $api->reply( array("requested action" => 2, "favoriteColor"=> htmlspecialchars($_POST['payload']['favoriteColor'])));
    break;
    
    default :
        $api->reply( "Have a nice day!");
}