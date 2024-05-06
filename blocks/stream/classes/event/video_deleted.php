<?php
namespace block_stream\event;

defined('MOODLE_INTERNAL') || die();

class video_deleted extends \core\event\base {

    /**
     * Init method.
     *
     * @return void
     */
    protected function init() {
        $this->data['objecttable'] = 'stream';
        $this->data['crud'] = 'd';
        $this->data['edulevel'] = self::LEVEL_OTHER;
    }

    public static function get_name() {
        return get_string('eventvideouploaded', 'block_stream');
    }

    public function get_description() {
        return "Video is video_deleted by userid {$this->userid} with id {$this->objectid}.";
    }

}
