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
 * Used to convert a bootswatch file from https://bootswatch.com/ to a Moodle preset.
 *
 * @package    theme_bloom
 * @subpackage db
 * @copyright  NULL0NULL3 Rakesh Kumar
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */


/**
 * Adds bloom to boost usertours
 *
 * @return bool
 */
function xmldb_theme_bloom_install() {
    global $DB;
            
    // for default set theme with bloom .
    set_config('theme', 'bloom');
    
    // for default set forcelogin .
    set_config('forcelogin', 1);
    
    // for default guest login disable .
    set_config('guestloginbutton', 0);
    
    // for delete not required block instace to user.
    if($DB->get_record('block_instances', array('blockname' => 'recentlyaccesseditems'))){
        $DB->delete_records('block_instances', array('blockname'=>'recentlyaccesseditems'));
    }
    if($DB->get_record('block_instances', array('blockname' => 'timeline'))){
        $DB->delete_records('block_instances', array('blockname'=>'timeline'));
    }
    if($DB->get_record('block_instances', array('blockname' => 'calendar_month'))){
        $DB->delete_records('block_instances', array('blockname'=>'calendar_month'));
    }
        
    // for default set block .
    $page = new moodle_page();
    $page->set_context(context_system::instance());
    if(empty($DB->get_record('block_instances', array('blockname' => 'timetable')))){
        $page->blocks->add_blocks(['layerone_full' => ['timetable']],'blocks-timetable-view', NULL, 0, 0);
    }
    if(empty($DB->get_record('block_instances', array('blockname' => 'quiz_graph')))){
        $page->blocks->add_blocks(['tabtwo_four' => ['quiz_graph']],'my-index', NULL, 1, -12);
    }
    if(empty($DB->get_record('block_instances', array('blockname' => 'assignments_graph')))){
        $page->blocks->add_blocks(['tabtwo_three' => ['assignments_graph']],'my-index', NULL, 1, -10);
    }
    if(empty($DB->get_record('block_instances', array('blockname' => 'teachers_tests_summary')))){
        $page->blocks->add_blocks(['tabtwo_four' => ['teachers_tests_summary']],'my-index', NULL, 1, -10);
    }
    if(empty($DB->get_record('block_instances', array('blockname' => 'assignments')))){
        $page->blocks->add_blocks(['tabtwo_three' => ['assignments']],'my-index', NULL, 1, -10);
    }
    if(empty($DB->get_record('block_instances', array('blockname' => 'student_program_info')))){
        $page->blocks->add_blocks(['tabtwo_three' => ['student_program_info']],'my-index', NULL, 1, -10);
    }
    if(empty($DB->get_record('block_instances', array('blockname' => 'semester_progress')))){
        $page->blocks->add_blocks(['tabone_one' => ['semester_progress']],'my-index', NULL, 1, -9);
    }
    if(empty($DB->get_record('block_instances', array('blockname' => 'student_todays_timetable')))){
        $page->blocks->add_blocks(['tabone_two' => ['student_todays_timetable']],'my-index', NULL, 1, -9);
    }
    if(empty($DB->get_record('block_instances', array('blockname' => 'studentblocks')))){
        $page->blocks->add_blocks(['tabtwo_four' => ['studentblocks']],'my-index', NULL, 1, -10);
    }
    if(empty($DB->get_record('block_instances', array('blockname' => 'quick_navigation')))){
        $page->blocks->add_blocks(['tabone_one' => ['quick_navigation']],'my-index', NULL, 1, -5);
    }
    if(empty($DB->get_record('block_instances', array('blockname' => 'teacher_courses')))){
        $page->blocks->add_blocks(['tabone_one' => ['teacher_courses']],'my-index', NULL, 1, -6);
    }
    if(empty($DB->get_record('block_instances', array('blockname' => 'employees_statistics')))){
        $page->blocks->add_blocks(['tabone_one' => ['employees_statistics']],'my-index', NULL, 1, -10);
    }
    if(empty($DB->get_record('block_instances', array('blockname' => 'student_statistics')))){
        $page->blocks->add_blocks(['tabone_one' => ['student_statistics']],'my-index', NULL, 1, -10);
    }
    if(empty($DB->get_record('block_instances', array('blockname' => 'todays_timetable')))){
        $page->blocks->add_blocks(['tabone_two' => ['todays_timetable']],'my-index', NULL, 1, -9);
    }
    if(empty($DB->get_record('block_instances', array('blockname' => 'queries')))){
        $page->blocks->add_blocks(['tabthree_one' => ['queries']],'my-index', NULL, 1, -10);
    }
    if(empty($DB->get_record('block_instances', array('blockname' => 'badges')))){
        $page->blocks->add_blocks(['tabthree_two' => ['badges']],'my-index', 2, 0, 0);
    }
    if(empty($DB->get_record('block_instances', array('blockname' => 'calendar_upcoming')))){
        $page->blocks->add_blocks(['tabone_two' => ['calendar_upcoming']],'my-index', NULL, 1, -7);
    }
    if(empty($DB->get_record('block_instances', array('blockname' => 'students_attendance')))){
        $page->blocks->add_blocks(['tabone_one' => ['students_attendance']],'my-index', NULL, 1, -7);
    }
    if(empty($DB->get_record('block_instances', array('blockname' => 'general_message')))){
        $page->blocks->add_blocks(['tabone_one' => ['general_message']],'my-index', NULL, 1, 0);
    }
    if(empty($DB->get_record('block_instances', array('blockname' => 'announcement')))){
        $page->blocks->add_blocks(['tabone_two' => ['announcement']],'my-index', NULL, 1, -8);
    }
    if(empty($DB->get_record('block_instances', array('blockname' => 'announcement')))){
        $page->blocks->add_blocks(['tabone_one' => ['open_courses']],'my-index', NULL, 1, -7);
    }   
    
    return true;
}


