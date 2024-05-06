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
 * @subpackage  admissions
 * @author eabyas  <info@eabyas.in>
**/

defined('MOODLE_INTERNAL') || die;
$functions = array(
    'local_admissions_accept_admission' => array(
        'classname'   => 'local_admissions_external',
        'methodname'  => 'accept_admission',
        'classpath'   => 'local/admissions/classes/external.php',
        'description' => 'Accept admission',
        'type'        => 'write',
        'ajax' => true,
    ),
    'local_admissions_status_statusconfirmform' => array(
        'classname'   => 'local_admissions_external',
        'methodname'  => 'statusconfirmform',
        'classpath'   => 'local/admissions/classes/external.php',
        'description' => 'Reject admission',
        'type'        => 'write',
        'ajax' => true,
    ),
    'local_admissions_status_rejectconfirmform' => array(
        'classname'   => 'local_admissions_external',
        'methodname'  => 'rejectconfirmform',
        'classpath'   => 'local/admissions/classes/external.php',
        'description' => 'Reject admission',
        'type'        => 'write',
        'ajax' => true,
    ),
);
