<?php
/**
 * This file is part of eAbyas
 *
 * Copyright eAbyas Info Solutons Pvt Ltd, India
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 *
 * @author eabyas  <info@eabyas.in>
 * @package BizLMS
 * @subpackage blocks_announcement
 */
namespace block_announcement;

class announcement {

	public function list_announcements() {
        global $DB, $USER;
        
        $params = array('courseid' => 1);
        // $systemcontext = \context_system::instance();

    	$sql = "SELECT * FROM {block_announcement} WHERE courseid = :courseid";
       	$sql .= " ORDER BY id DESC ";

        $announcements = $DB->get_records_sql($sql, $params);

        return $announcements;
    }

    public function get_announcement_attachments($announcement) {
        global $CFG;
        require_once($CFG->libdir. '/filestorage/file_storage.php');

        $fs = get_file_storage();
        $systemcontext = \context_system::instance();
        $attachments = $fs->get_area_files($systemcontext->id, 'block_announcement', 'announcement', $announcement->attachment, 'filename', false);
        $files = array();
        if ($attachments) {
            foreach ($attachments as $file) {
                $fileurl = \moodle_url::make_webservice_pluginfile_url($file->get_contextid(), $file->get_component(),
                                                                        $file->get_filearea(), null, $file->get_filepath(),
                                                                        $file->get_filename())->out(false);
                $files[] = array(
                    'filename' => $file->get_filename(),
                    'fileurl' => $fileurl,
                    'filesize' => $file->get_filesize(),
                    'filepath' => $file->get_filepath(),
                    'mimetype' => $file->get_mimetype(),
                    'timemodified' => $file->get_timemodified(),
                );
            }
        }
        return $files;
    }

}
