<?php
/**
 * This plugin is used to access stream video
 * @package    repository_stream
 */
require_once($CFG->dirroot . '/repository/lib.php');
require_once($CFG->dirroot.'/repository/stream/streamlib.php');

/**
 * This plugin is used to access user's private stream video
 * @package    repository_stream
 */
class repository_stream extends repository {
    /** @var int maximum number of thumbs per page */
    const STREAM_THUMBS_PER_PAGE = 10;
    public $listingUrl;
    /**
     * stream plugin constructor
     * @param int $repositoryid
     * @param object $context
     * @param array $options
     */
  public function __construct($repositoryid, $context = SYSCONTEXTID, $options = array()) {
        parent::__construct($repositoryid, $context, $options);
		$this->api_url  = $this->get_option('api_url');
		$this->api_key = $this->get_option('api_key');
        $this->secret  = $this->get_option('secret');
		$this->email_address  = $this->get_option('email_address');
		$this->user_name  = $this->get_option('user_name');
		$this->stream = new phpstream($this->api_url, $this->api_key, $this->secret, $this->email_address, $this->user_name);
    }

    public function check_login() {
        return true;
    }

    /**
     * @param array $options
     * @return mixed
     */

     public function set_option($options = array()) {
        if (!empty($options['api_key'])) {
            set_config('api_key', trim($options['api_key']), 'stream');
        }
        if (!empty($options['secret'])) {
            set_config('secret', trim($options['secret']), 'stream');
        }
	if (!empty($options['user_name'])) {
            set_config('user_name', trim($options['user_name']), 'stream');
        }
        if (!empty($options['email_address'])) {
            set_config('email_address', trim($options['email_address']), 'stream');
        }
	if (!empty($options['api_url'])) {
            set_config('api_url', trim($options['api_url']), 'stream');
        }

        unset($options['api_key']);
        unset($options['secret']);
        unset($options['api_url']);
        unset($options['email_address']);
		unset($options['user_name']);
        $ret = parent::set_option($options);
        return $ret;
    }

    /**
     * @param string $config
     * @return mixed
     */

   public function get_option($config = '') {
        if ($config==='api_key') {
            return trim(get_config('stream', 'api_key'));
        } elseif ($config ==='secret') {
            return trim(get_config('stream', 'secret'));
        } elseif ($config ==='email_address') {
            return trim(get_config('stream', 'email_address'));
        } elseif ($config ==='user_name') {
            return trim(get_config('stream', 'user_name'));
        } elseif ($config ==='api_url') {
            return trim(get_config('stream', 'api_url'));
        }else {
            $options['api_key'] = trim(get_config('stream', 'api_key'));
            $options['secret']  = trim(get_config('stream', 'secret'));
	    $options['api_url'] = trim(get_config('stream', 'api_url'));
            $options['email_address']  = trim(get_config('stream', 'email_address'));
	    $options['user_name']  = trim(get_config('stream', 'user_name'));
        }
        $options = parent::get_option($config);
        return $options;
    }

   /**
     * Add Plugin settings input to Moodle form
     * @param object $mform
     */
    public static function type_config_form($mform, $classname = 'repository') {
        global $CFG;
        $api_key = get_config('stream', 'api_key');
        $secret = get_config('stream', 'secret');
    	$api_url = get_config('stream', 'api_url');

        if (empty($api_key)) {
            $api_key = '';
        }
	    if (empty($secret)) {
            $secret = '';
        }
        if (empty($api_url)) {
            $api_url = '';
        }
	    if (empty($email)) {
            $email = '';
        }
	    if (empty($user_name)) {
            $user_name = '';
        }

        parent::type_config_form($mform);

        $strrequired = get_string('required');
		$mform->addElement('text', 'api_url', get_string('apiurl', 'repository_stream'), array('value'=>$api_url,'size' => '40'));
        $mform->addHelpButton('api_url', 'api_url_stream', 'repository_stream');
        $mform->setType('api_url', PARAM_RAW_TRIMMED); 
        $mform->addElement('static', 'pluginnamehelp', '', get_string('api_url_stream_help', 'repository_stream'));
       
        $mform->addElement('text', 'api_key', get_string('apikey', 'repository_stream'), array('value'=>$api_key,'size' => '40'));
        $mform->setType('api_key', PARAM_RAW_TRIMMED);
        $mform->addHelpButton('api_key', 'api_key_stream', 'repository_stream');

        $mform->addElement('text', 'secret', get_string('secret', 'repository_stream'), array('value'=>$secret,'size' => '40'));
        $mform->addHelpButton('secret', 'secret_stream', 'repository_stream');
        $mform->setType('secret', PARAM_RAW_TRIMMED);

        $mform->addRule('api_key', $strrequired, 'required', null, 'client');
        $mform->addRule('secret', $strrequired, 'required', null, 'client');
        $mform->addRule('api_url', $strrequired, 'required', null, 'client');
    }

  public function search($search_text, $page = 0) {
        $ret  = array();
        $ret['nologin'] = true;
        $ret['page'] = (int)$page;
        if ($ret['page'] < 1) {
            $ret['page'] = 1;
        }

        $start = ($ret['page'] - 1) * self::STREAM_THUMBS_PER_PAGE + 1;
        $max = self::STREAM_THUMBS_PER_PAGE;
		$start = $start-1;
        $this->search_url = $this->stream->createSearchApiUrl();

        $params = $this->stream->get_listing_params();
        $params['q'] = $search_text;
        $params['sort'] = $sort;
        $params['perpage'] = self::STREAM_THUMBS_PER_PAGE;
		$request = new curl();
        $content = $request->post($this->search_url, $params);

		$content = json_decode($content,true);
        // $params = array(
        //     'context' => $context,
        //     'objectid' => $content
        // );
        // $eventcheck = \repository_stream\event\get_videos::create($params);
        // $eventcheck->trigger();
        $ret['list'] = $this->_get_collection($content);
        $ret['norefresh'] = true;
        $ret['nosearch'] = false;
		$ret['total'] = $content['meta']['total'];
		$ret['pages'] = ceil($content['meta']['total']/self::STREAM_THUMBS_PER_PAGE);
        $ret['perpage'] = self::STREAM_THUMBS_PER_PAGE;
        return $ret;
    }

    /**
     * Private method to get video list
     */
    private function _get_collection($content) {
        $list = array();
    	if(count($content['data']) > 0) {
    		foreach ($content['data'] as $entry) {
    			$list[] = array(
    		        'shorttitle'=>$entry['title'],
    		        'thumbnail_title'=>$entry['title'],
    		        'title'=>$entry['title'].'.avi', // this is a hack so we accept this file by extension
                    'thumbnail'=> $this->api_url.stripslashes($entry['thumbnail']),
    		        'videoid'=>stripslashes($entry['videoid']),
    		        'thumbnail_width'=>150,
    		        'thumbnail_height'=>150,
    		        'size'=>1*1024*1024,
    		        'date'=>strtotime($entry['timecreated']),
    				'license'=>'unknown',
    				'author'=>$entry['usercreated'],
                    'source'=>$entry['path']
        		);
    		}
    	}
		return $list;

    }

    public static function get_type_option_names() {
        return array('api_key', 'secret', 'api_url', 'pluginname');
    }

    /**
     * file types supported by stream plugin
     * @return array
     */
    public function supported_filetypes() {
        return array('video');
    }

    /**
     * Is this repository accessing private data?
     * @return bool
     */
    public function contains_private_data() {
        return false;
    }

    /**
     * Tells how the file can be picked from this repository
     *
     * @return int
     */
    public function supported_returntypes() {
        return FILE_EXTERNAL;
    }

    /**
     * Does this repository used to browse moodle files?
     *
     * @return boolean
     */
    public function has_moodle_files() {
        return false;
    }

    public function get_listing($path='', $page = '') {
        global $OUTPUT;
        $folderUrl = $OUTPUT->pix_url('f/folder-128')->out();
        $this->listingUrl = $this->stream->createListingApiUrl();
        $params = $this->stream->get_listing_params();
        $params['currentPath'] = $path ? $path : '/';
        $params['search'] = null;
        $request = new curl();
        $content = $request->post($this->listingUrl, $params);

        $content = json_decode($content,true);
        // $params = array(
        //     'context' => $context,
        //     'objectid' => $content
        // );
        // $eventcheck = \repository_stream\event\get_videos_value::create($params);
        // $eventcheck->trigger();
        $content['forganizations'] = array();
        $content['fdirectories'] = array();
        $folderlists = array_merge($content['forganizations'], $content['fdirectories']);
        $filesList = $content['fvideos'];
        $return =array('dynload' => true, 'nosearch' => false, 'nologin' => true);
        foreach($content['navPath'] AS $paths){
            $pathelement = array(
                'icon' => $OUTPUT->image_url(file_folder_icon(90))->out(false),
                'path' => $paths['navpathdata'],
                'name' => $paths['name']
            );
            $return['path'][] = $pathelement;

        }
        $return['list'] = array();
        foreach($folderlists AS $folders){
            $listelement = array();
            $listelement['thumbnail'] = $folderUrl;
            $listelement['thumbnail_width'] = 90;
            $listelement['thumbnail_height'] = 90;
            $listelement['title'] = $folders['fullname'];
            $listelement['path'] = $folders['path'];
            $listelement['children'] = [];
            $return['list'][] = $listelement;
        }
        foreach($filesList AS $files){
            $filecontent = array(
                'thumbnail' => $this->api_url.'/storage/'.$files['thumbnail'],
                'title' => $files['title'].'.avi',
                'source' => $files['encodedurl'],
                'date'=>strtotime($files['timecreated']),
                'license'=>'unknown',
                'thumbnail_title'=>$files['title'],
                'encoded_url' =>$files['encodedurl']
            );
            $return['list'][] = $filecontent;
        }
        return $return;
        
    }


}
