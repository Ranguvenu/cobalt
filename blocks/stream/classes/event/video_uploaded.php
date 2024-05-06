<?php
namespace block_stream\event;

defined('MOODLE_INTERNAL') || die();

class video_uploaded extends \core\event\base {

    /**
     * Init method.
     *
     * @return void
     */
    protected function init() {
        $this->data['objecttable'] = 'stream';
        $this->data['crud'] = 'c';
        $this->data['edulevel'] = self::LEVEL_OTHER;
    }

    public static function get_name() {
        return get_string('eventvideouploaded', 'block_stream');
    }

    public function get_description() {
        return "Video is uploaded by userid {$this->userid} with status {$this->objectid}.";
    }

    public function get_url() {
        return new \moodle_url('blocks/stream/index.php',
            array('id' => $this->objectid));
    }
}
