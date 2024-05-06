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
 * @package
 * @subpackage local_employees
 */

require_once(dirname(__FILE__) . '/../../config.php');
require_once($CFG->libdir . '/adminlib.php');
$format = optional_param('format', 'csv', PARAM_ALPHA);
$systemcontext = context_system::instance();
if(!(has_capability('local/employees:manage', $systemcontext) && has_capability('local/employees:create', $systemcontext))){
    echo print_error('no permission');
}
$labelstring = get_config('local_costcenter');
$firstlevel = strtolower($labelstring->firstlevel);
$secondlevel = strtolower($labelstring->secondlevel);
$thirdlevel = strtolower($labelstring->thirdlevel);
if ($format) {
    if(is_siteadmin() || has_capability('local/costcenter:manage_multiorganizations', $systemcontext)){
        $fields = array(
            'organization' => $firstlevel,
            'first_name' => 'first_name',
            'last_name' => 'last_name',
            'password' => 'password',
            'email' => 'email',
            'staff_status' => 'staff_status',
            'phone' => 'phone',
            'city' => 'city',
            'address' => 'address',
            'department' => $secondlevel,
            'subdepartment' => $thirdlevel,
            'role' => 'role',
        );
    }
    if(!is_siteadmin() && !has_capability('local/costcenter:manage_multiorganizations', $systemcontext) && has_capability('local/costcenter:manage_ownorganization', $systemcontext)){
        $fields = array(
            // 'organization' => $firstlevel,
            'first_name' => 'first_name',
            'last_name' => 'last_name',
            'password' => 'password',
            'email' => 'email',
            'staff_status' => 'staff_status',
            'phone' => 'phone',
            'city' => 'city',
            'address' => 'address',
            'department' => $secondlevel,
            'subdepartment' => $thirdlevel,
            'role' => 'role',
        );
    }
    if(!is_siteadmin() && !has_capability('local/costcenter:manage_multiorganizations', $systemcontext) && !has_capability('local/costcenter:manage_ownorganization', $systemcontext) && has_capability('local/costcenter:manage_owndepartments', $systemcontext)){
        $fields = array(
            // 'organization' => $firstlevel,
            'first_name' => 'first_name',
            'last_name' => 'last_name',
            'password' => 'password',
            'email' => 'email',
            'staff_status' => 'staff_status',
            'phone' => 'phone',
            'city' => 'city',
            'address' => 'address',
            // 'department' => $secondlevel,
            'subdepartment' => $thirdlevel,
            'role' => 'role',
        );
    }
    if(!is_siteadmin() && !has_capability('local/costcenter:manage_multiorganizations', $systemcontext) && !has_capability('local/costcenter:manage_ownorganization', $systemcontext) && !has_capability('local/costcenter:manage_owndepartments', $systemcontext) && has_capability('local/costcenter:manage_ownsubdepartments', $systemcontext)){
        $fields = array(
            // 'organization' => $firstlevel,
            'first_name' => 'first_name',
            'last_name' => 'last_name',
            'password' => 'password',
            'email' => 'email',
            'staff_status' => 'staff_status',
            'phone' => 'phone',
            'city' => 'city',
            'address' => 'address',
            // 'department' => $secondlevel,
            // 'subdepartment' => $thirdlevel,
            'role' => 'role',
        );
    }
    switch ($format) {
        case 'csv' : user_download_csv($fields);
    }
    die;
}
function user_download_csv($fields) {
    global $CFG;
    require_once($CFG->libdir . '/csvlib.class.php');
    $csvexport = new csv_export_writer();
    $csvexport->set_filename($filename);
    $csvexport->add_data($fields);
	$userprofiledata = array();
	$csvexport->add_data($userprofiledata);
    $csvexport->download_file();
    die;
}