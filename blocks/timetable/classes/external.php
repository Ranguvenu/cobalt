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
 */
defined('MOODLE_INTERNAL') || die;

use block_timetable\output\eventlist;
require_once(dirname(__FILE__) . '/../../../calendar/lib.php');

class block_timetable_external extends external_api{
	public static function get_timetable_parameters(){
		return new external_function_parameters(
            array(
                'type' => new external_value(PARAM_RAW, 'type', false, 'today')
            )
        );
	}
	public static function get_timetable($type){
		global $PAGE, $COURSE;
		$params = self::validate_parameters(
            self::get_timetable_parameters(),
            [
                'type' => $type
            ]
        );

        $datestarttime = date("Y").'-'.date("m").'-'.date("d").' 00:00:00';
        $dateendtime = date("Y").'-'.date("m").'-'.date("d").' 23:59:59';

$lastdate = strtotime($dateendtime);
$page = 1;
$limitnum = 1000;
$limitfrom = 0;

$lastid = 0;
$lastdate = 0;
$blockview = 'vertical';
$time = strtotime('today midnight');
$lookahead = get_user_preferences('calendar_lookahead', 6);
        $events1 = new eventlist(
    true,
    $COURSE->id,
    $lastid,
    $lastdate,
    $limitfrom,
    $limitnum,
    $page,
    $blockview,
    $time,
    0,
    $type
);
		list($more, $events) = $events1->get_timetabevents(
            true,
            $COURSE->id,
            $lastid,
            $lastdate,
            $limitfrom,
            $limitnum
            );

        return array('events' => $events);
	}
	public static function get_timetable_returns(){

        return new external_single_structure([
            'events' => new external_multiple_structure(
                new external_single_structure([
                    'id' => new external_value(PARAM_INT, 'id'),
                    'name' => new external_value(PARAM_TEXT, 'name'),
                    'description' => new external_value(PARAM_TEXT, 'description'),
                    'component' => new external_value(PARAM_RAW, 'component'),
                    'modulename' => new external_value(PARAM_TEXT, 'modulename'),
                    'activityname' => new external_value(PARAM_TEXT, 'activityname'),
                    'activitystr' => new external_value(PARAM_RAW, 'activitystr'),
                    'instance' => new external_value(PARAM_INT, 'instance', VALUE_OPTIONAL),
                    'eventtype' => new external_value(PARAM_RAW, 'eventtype'),
                    'timestart' => new external_value(PARAM_INT, 'timestart'),
                    'timeduration' => new external_value(PARAM_INT, 'timeduration'),
                    'timesort' => new external_value(PARAM_INT, 'timesort'),
                    'visible' => new external_value(PARAM_BOOL, 'visible', VALUE_OPTIONAL),
                    'formattedtime' => new external_value(PARAM_RAW, 'visible', VALUE_OPTIONAL),
                    'url' => new external_value(PARAM_URL, 'url', VALUE_OPTIONAL),
                ])
            )
        ]);
	}
}
