<?php
// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

/**
 * Course list block.
 *
 * @package    block_custom_user_list
 * @copyright  jyoti
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */



class block_myprograms extends block_base {


    function init() {
        $this->title = get_string('pluginname','block_myprograms');
    }
    public function get_content() {
    if ($this->content !== null) {
      return $this->content;
    }
    global $DB, $USER;
    $userid = $USER->id;
   // $this->content         =  new stdClass;
     $renderable = new block_myprograms\output\main();
    $renderer = $this->page->get_renderer('block_myprograms');

        $this->content = new stdClass();
        $sql = $DB->get_records_sql("SELECT me.name,me.id,me.description
              FROM {local_program_users} AS mue
             INNER JOIN {local_program} AS me
        WHERE mue.programid = me.id AND mue.userid =".$userid."");
         if (!$sql) {
     // list is empty.
          $this->content->text = "<p>You are not assigned to any program.</p>";
        }
        else
        {
        $this->content->text = $renderer->render($renderable);
        }

    return $this->content;
	}

  }
