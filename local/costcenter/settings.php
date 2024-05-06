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
 * Resource module admin settings and defaults
 *
 * @package    mod_label
 * @copyright  2013 Davo Smith, Synergy Learning
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die;

$settings = new admin_settingpage('local_costcenter', get_string('settingsname', 'local_costcenter'));
$ADMIN->add('localplugins', $settings);

// $settings->add(new admin_setting_heading('local_costcenter/pluginname','',
//             get_string('connectionsettings_desc', '')));

// $settings->add(new admin_setting_description('description','',get_string('settingsdesc','local_costcenter')));

// $name = new lang_string('pluginname', 'local_costcenter');
$description = new lang_string('settingsdesc', 'local_costcenter');
$settings->add(new admin_setting_heading('defaultsettings', '', $description));


 // $choices['Institution'] = new lang_string('institution', 'local_costcenter');
 //    $choices['University'] = new lang_string('university', 'local_costcenter');
 //    $settings->add(new admin_setting_configselect('firstlevel', new lang_string('firstlevel', 'local_costcenter'),
 //            new lang_string('firstlevelstring', 'local_costcenter'), get_default_home_page(), $choices));


$name = new lang_string('firstlevel', 'local_costcenter');
    $options = array(
        'University' => get_string('university', 'local_costcenter'),
        'Institution' => get_string('orgtion', 'local_costcenter'),
    );
    $description = new lang_string('firstlevelstring', 'local_costcenter');
    $settings->add(new admin_setting_configselect('local_costcenter/firstlevel',
                                                    $name,
                                                    $description,
                                                    'University',
                                                    $options));


// $settings->add(new admin_setting_configtext('local_costcenter/firstlevel',
//     get_string('firstlevel', 'local_costcenter'), get_string('firstlevelstring', 'local_costcenter'), get_string('orgtion', 'local_costcenter'), PARAM_TEXT, 50));

$settings->add(new admin_setting_configtext('local_costcenter/secondlevel',
    get_string('secondlevel', 'local_costcenter'), get_string('secondlevelstring','local_costcenter'), get_string('colg', 'local_costcenter'), PARAM_TEXT, 50));

$settings->add(new admin_setting_configtext('local_costcenter/thirdlevel',
    get_string('thirdlevel', 'local_costcenter'), get_string('thirdlevelstring', 'local_costcenter'), get_string('dept', 'local_costcenter'), PARAM_TEXT, 50));

