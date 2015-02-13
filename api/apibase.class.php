<?php
/**
 * APIBASE Framework
 */
include_once 'config.php';
 
class apibase {
    
    public $user = false;
    private $users_key = ''; // decrypted api_key will be set below

    /**
     * Receive user's _POST package and ensure it contains the following:
     *  - sid (User's unique identifier)
     *  - payload (The string or array of data being passed into the API)
     *  - signature (A hash of the payload combined with the user's secret API Key)
     * Ensures that the given user is allowed to access the API and that their IP address has been whitelisted
     * Validates the signature hash against the user's known secret api key
     */
	public function __construct()
    {
        header("Expires: Sat, 07 Apr 1979 05:00:00 GMT");
        header("Last-Modified: " . gmdate("D, d M Y H:i:s") . " GMT");
        header("Cache-Control: no-store, no-cache, must-revalidate");
        header("Cache-Control: post-check=0, pre-check=0", false);
        header("Pragma: no-cache");
        header('Content-Type: application/json');
        
        // make sure request contains the user security identifier 
        if(!isset($_POST['sid']))
        {
            $this->reply(array("ERROR"=>"Missing SID.  Please request an API user account."),"400 Bad Request");  
        }
        
        // make sure some data was passed in
        if(!isset($_POST['payload']) || trim($_POST['payload']) == "")
        {
            $this->reply(array("ERROR"=>"Missing payload.  Request does not include any information."),"400 Bad Request");  
        }
        
        // make sure the request is signed
        if(!isset($_POST['signature']))
        {
            $this->reply(array("ERROR"=>"Unsigned Request. Please include Hashed Message Authentication Code."),"403 Forbidden"); 
        }
        
        // look up user (do this on every request incase account status has changed)
        $this->user = ORM::for_table('api_accounts')->where('sid',$_POST['sid'])->find_one();
        if(!$this->user)
        {
            $this->reply(array("ERROR"=>"Invalid SID. The given value does not belong to a known user."),"403 Forbidden"); 
        }
        
        // if user has white-listed an IP address (or addresses), make sure they are using it.
        if($this->user->valid_ip_list != "")
        {
            $ips = explode(",", $this->user->valid_ip_list);
            if(!in_array($_SERVER['REMOTE_ADDR'], $ips))
            {
                $this->reply(array("ERROR"=>"Requesting IP address not assoicated with given user."),"403 Forbidden");    
            }
        }
        
        // make sure user's account has access to the API
        if($this->user->active != 1)
        {
            $this->reply(array("ERROR"=>"This user is not currently authorized to access the API."),"403 Forbidden");    
        }

        // check that the signature matches the user
        $this->users_key = decrypt($this->user->api_key);
        $expected = base64_encode(hash_hmac(hash_algo, print_r($_POST['payload'],true), $this->users_key ));
        
        if($_POST['signature'] !== $expected)
        {
           $this->reply(array("ERROR"=>"Invalid HMAC signature. Please refer to documentation for proper hashing method."),"403 Forbidden"); 
        }
        
        $_POST['payload'] = json_decode($_POST['payload'],true); // (parm 2 = "true" to keep it an assoc array)
	} 
	
	/**
	 * Return a JSON encoded Package to the user
	 */
	public function reply($string_or_array, $status = "200 Success")
	{
	    $package['timestamp'] = gmdate("c");
	    $package['payload'] = $string_or_array;
	    
	    if(isset($this->user->api_key))
	    {
    	    $package['signature'] = base64_encode(hash_hmac(hash_algo, print_r($string_or_array,true), $this->users_key ));	        
	    }

	    header("HTTP/1.0 ". $status );
	    echo json_encode($package);
	    exit();
	}
}