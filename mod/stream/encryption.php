<?php

require_once('../../config.php');
global $CFG;
require_login();
require_once($CFG->dirroot.'/repository/stream/streamlib.php');

$api_key = trim(get_config('stream', 'api_key'));
$secret  = trim(get_config('stream', 'secret'));
$api_url = trim(get_config('stream', 'api_url'));
$email_address  = trim(get_config('stream', 'email_address'));
$user_name  = trim(get_config('stream', 'user_name'));
        
$stream = new phpstream($api_url, $api_key, $secret, $email_address, $user_name);
$url = required_param('uri',  PARAM_RAW);

echo $stream->get_encryption_token($url);