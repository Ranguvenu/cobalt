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
 * Plans to review renderable.
 *
 * @package    block_lp
 * @copyright  2016 Frédéric Massart - FMCorz.net
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
namespace block_myprograms\output;
defined('MOODLE_INTERNAL') || die();

use renderable;
use templatable;
use renderer_base;
use stdClass;
use moodle_url;
use core_competency\api;
use core_competency\external\plan_exporter;
use core_user\external\user_summary_exporter;

/**
 * Plans to review renderable class.
 *
 * @package    block_lp
 * @copyright  2016 Frédéric Massart - FMCorz.net
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class myprogram implements renderable, templatable {

    /** @var array Plans to review. */

    /**
     * Export the data.
     *
     * @param renderer_base $output
     * @return stdClass
     */
    public function export_for_template(renderer_base $output) {
        $data = new stdClass();

        global $USER, $DB,$CFG;
         $userid = $USER->id;

        $programid = $_GET['id'];
         $sql = $DB->get_recordset_sql("SELECT * FROM {local_program}  
                WHERE id  = ".$programid."");

        $levels = $DB->get_recordset_sql("SELECT *
                      FROM {local_program_levels} 
                WHERE programid = ".$programid."");
      

        $data = array(
            'programname' => $sql->name,
            'levels' => $levels->level,
            'leveldes' =>$levels->description,
            'program' => $sql,
            'levelloop' => $levels,
        );

        return $data;
    }

}
