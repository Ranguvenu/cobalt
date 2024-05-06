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
 * Curriculum list block.
 *
 * @package    block_mycurriculum
 * @copyright  diksha@eabyas.com
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
//use block_mycurriculum\output;
class block_mycurriculum extends block_base {
    public function init() {
        
         $this->title = get_string('mycurriculum', 'block_mycurriculum');
    }
    // The PHP tag and the curly bracket for the class definition 
    // will only be closed after there is another function added in the next section.

    public function get_content() {
        if ($this->content !== null) {
          return $this->content;
        }
        global $DB,  $USER, $PAGE;
        $userid = $USER->id;
        // $this->content         =  new stdClass;
        $renderable = new \block_mycurriculum\output\main();

        $renderer = $this->page->get_renderer('block_mycurriculum');

        $this->content = new stdClass();
        $sql = $DB->get_records_sql("SELECT me.name,me.id,lc.name
              FROM {local_program_users} AS mue
              INNER JOIN {local_program} AS me 
              JOIN {local_curriculum} as lc ON me.curriculumid = lc.id
              WHERE mue.programid = me.id AND mue.userid =".$userid."");
        
        foreach($sql as $sqls){
            $name= $sqls->name;
        }
        if (!$name) {
        // list is empty.
          $this->content->text = "<p>You are not assigned to any curriculum.</p>";
        }
        else {
            $this->content->text = $renderer->render($renderable); 
        }
        $this->title = get_string('mycurriculum', 'block_mycurriculum') .'-' . $name;
    return $this->content;
    }
}
