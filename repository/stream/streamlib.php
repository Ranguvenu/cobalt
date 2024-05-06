<?php
define('HEX2BIN_WS', " \t\n\r");
class phpstream {
    public $client_id;
    public $secret;
    public $email;
    public $name;
    public $xauth_token;
    public $api_url;

    function __construct ($api_url, $client_id, $secret, $email_address, $user_name)
    {
        global $CFG;
        require_once($CFG->libdir.'/filelib.php');
        //The API Key must be set before any calls can be made.
		$this->api_url = $api_url;
    	$this->client_id = $client_id;
    	$this->secret = $secret;
		$this->email = $email_address;
		$this->name = $user_name;
		$this->xauth_token='';
    }

   	public function createSearchApiUrl() 
    {
 		return $this->api_url."/api/v1/videos/index";
  	}

  	public function get_listing_params()
    {
        global $CFG;
  		$tokenurl = $this->api_url."/api/v1/apis/token";
  		$c = new \curl();
        $tokenjson = $c->post($tokenurl, array('key'=> $this->client_id, 'secret' => $this->secret, 'domain' => $CFG->wwwroot));
        $tokenInfo = json_decode($tokenjson);
        $token = $tokenInfo->token;
        $params = array('token' => $token);

        return $params;
  	}

    public function get_encryption_token($url)
    {
        global $CFG;
        $encryptionurl = $this->api_url."/api/v1/videos/encryption";
        $c = new \curl();
        $params = $this->get_listing_params();
        $params['key'] = $this->client_id;
        $params['secret'] = $this->secret;
        $params['uri'] = $url;
        $content = $c->get($encryptionurl, $params);
        return $content;
    }
    public function createListingApiUrl()
    {
        return $this->api_url."/api/v1/videos/fvideos";
    }
    public function get_upload_data()
    {
        $search_url = $this->api_url."/api/v1/videos/uploaddata";
        $c = new \curl();
        $params = $this->get_listing_params();
        $params['key'] = $this->client_id;
        $params['secret'] = $this->secret;
        $content = $c->post($search_url, $params);
        return $content;
    }

    public function get_videos($params)
    {
        $search_url = $this->createSearchApiUrl();
        $curlparams = $this->get_listing_params();
        $params = array_merge($curlparams, $params);

        $c = new \curl();
        $content = $c->post($search_url, $params);
       
        $content = json_decode($content,true);
        return $content;
    }
}