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
 * @package   local
 * @subpackage  ratings
 * @author eabyas  <info@eabyas.in>
**/
defined('MOODLE_INTERNAL') || die;
$functions = array(
	'block_timetable_get_timetable' => array(
        'classname'   => 'block_timetable_external',
        'methodname'  => 'get_timetable',
        'classpath'   => 'blocks/timetable/classes/external.php',
        'description' => 'timetable',
        'type'        => 'read',
        'services' => array(MOODLE_OFFICIAL_MOBILE_SERVICE),
    )
);