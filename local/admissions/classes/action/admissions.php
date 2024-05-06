<?php

namespace local_admissions\action;
use coding_exception;
use context_helper;
use context_system;
use core\invalid_persistent_exception;
use core\message\message;
use core_user;
use dml_exception;
use moodle_exception;
use moodle_url;
use required_capability_exception;
use stdClass;

defined('MOODLE_INTERNAL') || die();

class admissions {
    public static function uploaddocs_store($approvalletterfile) {

        global $DB, $USER,$CFG;

        // Check if backup directory exists,if not create it.
        check_dir_exists($CFG->tempdir . '/uploaddocs');

        // Where the file is going to be stored
        $target_dir = $CFG->tempdir.DIRECTORY_SEPARATOR."uploaddocs".DIRECTORY_SEPARATOR;

        $filename = $approvalletterfile['uploaddocs']['name'];
        // $ext = $path['extension'];
        $temp_name = $approvalletterfile['uploaddocs']['tmp_name'];

        $path_filename_ext = $target_dir.$filename; 
             
        // Check if file already exists


        if(move_uploaded_file($temp_name, $path_filename_ext)){
            //echo "Successfull";     
        }
          
        $content = file_get_contents($path_filename_ext);  


        $systemcontext = \context_system::instance();

        $record = [];

        $record['contextid'] = $systemcontext->id;
        $record['component'] = 'local_admissions';

        $record['filearea'] = 'uploaddocs';

        $params = [
                    'component' => $record['component'],
                    'filearea' => $record['filearea'],
                    'contextid' => $record['contextid'],
                    'filename' => '.',
                ];

        $contextid = $record['contextid'];

        $fs = get_file_storage();
        $draftitemid = rand(1, 999999999);
        while ($files = $fs->get_area_files($contextid, 'user', 'draft', $draftitemid)) {
            $draftitemid = rand(1, 999999999);
        }

        $record['itemid'] = $draftitemid;

        if (!isset($record['filepath'])) {
            $record['filepath'] = '/';
        }

        if (!isset($record['filename'])) {
            $record['filename'] = $filename ;
        }

        $fs = get_file_storage();

        $file=$fs->create_file_from_string($record, $content);

        $save_file_loc = $path_filename_ext;

        if (file_exists($save_file_loc)) {
            unlink($save_file_loc);
        }
            
        return $record['itemid'];
    }
}