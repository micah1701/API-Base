<?php
class apibase {
    
    var $public_sid  = ''; // these can be hard coded here 
    var $secret_key  = ''; // or passed to this class using the setCredentials() method
    
    var $api_url     = 'http://micahj.com/code/apibase/api/';
    
    /**
     * package up the payload and signature and submit to api
     * returns validated response
     */
    public function send($string_or_array)
    {
        $start_time = microtime(true);
        $package['sid'] = $this->public_sid;
        $package['payload'] = json_encode($string_or_array);
        $package['signature'] = $this->generateSignature($package['payload']);

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $this->api_url);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $this->array2string($package));
        curl_setopt($ch, CURLOPT_HEADER, 0);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true); // don't output response to browser
        $response = curl_exec($ch);
        $response_status = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        
    //    exit($response);
        
        $response_decoded = json_decode($response,true); // (param 2 as "true" makes it an assoc array)
       
        $output['valid_signature'] = (isset($response_decoded['signature']) && $response_decoded['signature'] == $this->generateSignature($response_decoded['payload'])) ? "TRUE" : "FALSE";
        $output['latency'] = microtime(true) - $start_time;
        $output['reponse_time'] = $response_decoded['timestamp'];
        $output['response_status'] = $response_status;
        $output['payload'] = $response_decoded['payload'];
        
        return $output;
    }
    
    /**
     * helper function to generate a base64 encoded Hashed Message Authentication Code
     */
    private function generateSignature($payload)
    {
        return base64_encode(hash_hmac('sha256', print_r($payload,true), $this->secret_key ));
    }

    /**
     * helper function to convert an array to a "&key=value" string
	 */
	private function array2string($myArray)
	{
		$string = "";
		foreach ($myArray as $key => $value)
		{
				$string.= $key."=".$value ."&";
		}
		return rtrim($string,"&");
	}
	
	/**
	 * Set the Security ID and Secret API Key from the calling script
	 */
	 public function setCredentials($sid,$key)
	 {
	    $this->public_sid = $sid;
	    $this->secret_key = $key;
	 } 
}