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
 * A drawer based layout for the bloom theme.
 *
 * @package   theme_bloom
 * @copyright 2021 Bas Brands
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

require_once($CFG->libdir . '/behat/lib.php');
require_once($CFG->dirroot . '/course/lib.php');
global $DB, $USER,$PAGE,$OUTPUT;
    $users=$DB->record_exists_sql("SELECT u.id FROM {user} u 
                            JOIN {role_assignments} ra on u.id=ra.userid 
                            JOIN {role} r on r.id=ra.roleid 
                            WHERE r.shortname='editingteacher' or 'student' 
                            and u.id=$USER->id");
$dashboard=$PAGE->pagelayout;
$editmodes=$USER->editing;
    if ($dashboard=='mydashboard') {
        $editing=1;
        if(is_siteadmin() && $editmodes==1){
            $return=1;
        }elseif(!is_siteadmin() || !empty($user)){
            $return=1;
        }else{
            $return=false;
        }
    }else{
        $editing=false;
    }
// Add block button in editing mode.
$addblockbutton = $OUTPUT->addblockbutton();

user_preference_allow_ajax_update('drawer-open-nav', PARAM_ALPHA);
user_preference_allow_ajax_update('drawer-open-index', PARAM_BOOL);
user_preference_allow_ajax_update('drawer-open-block', PARAM_BOOL);

if (isloggedin()) {
    $courseindexopen = (get_user_preferences('drawer-open-index', true) == true);
    $blockdraweropen = (get_user_preferences('drawer-open-block') == true);
} else {
    $courseindexopen = false;
    $blockdraweropen = false;
}

if (defined('BEHAT_SITE_RUNNING')) {
    $blockdraweropen = true;
}

$extraclasses = ['uses-drawers'];
if ($courseindexopen) {
    $extraclasses[] = 'drawer-open-index';
}

$blockshtml = $OUTPUT->blocks('side-pre');
$hasblocks = (strpos($blockshtml, 'data-block=') !== false || !empty($addblockbutton));
if (!$hasblocks) {
    $blockdraweropen = false;
}
$courseindex = core_course_drawer();
if (!$courseindex) {
    $courseindexopen = false;
}

$bodyattributes = $OUTPUT->body_attributes($extraclasses);
$forceblockdraweropen = $OUTPUT->firstview_fakeblocks();

$secondarynavigation = false;
$overflow = '';
if ($PAGE->has_secondary_navigation()) {
    $tablistnav = $PAGE->has_tablist_secondary_navigation();
    $moremenu = new \core\navigation\output\more_menu($PAGE->secondarynav, 'nav-tabs', true, $tablistnav);
    $secondarynavigation = $moremenu->export_for_template($OUTPUT);
    $overflowdata = $PAGE->secondarynav->get_overflow_menu_data();
    if (!is_null($overflowdata)) {
        $overflow = $overflowdata->export_for_template($OUTPUT);
    }
}

$primary = new core\navigation\output\primary($PAGE);
$renderer = $PAGE->get_renderer('core');
$primarymenu = $primary->export_for_template($renderer);

$regionleft = $OUTPUT->blocks('leftregion', 'col-md-8');
$regionright = $OUTPUT->blocks('rightregion', 'col-md-4');

$layerone_detail_full = $OUTPUT->blocks('layerone_full', 'col-md-12');
$layerone_detail_one = $OUTPUT->blocks('layerone_one', 'col-md-8 float-left');
$layerone_detail_oneone = $OUTPUT->blocks('layerone_oneone', 'col-md-8 float-left');
$layerone_detail_two = $OUTPUT->blocks('layerone_two', 'col-md-4 float-left');

$layertwo_detail_one = $OUTPUT->blocks('layertwo_one', 'col-md-12');
$layertwo_detail_two = $OUTPUT->blocks('layertwo_two', 'col-md-12');
$layertwo_detail_three = $OUTPUT->blocks('layertwo_three', 'col-md-6 float-left');
$layertwo_detail_four = $OUTPUT->blocks('layertwo_four', 'col-md-6 float-left');

$layertwo_three_one = $OUTPUT->blocks('layerthree_one', 'col-md-12');
$layertwo_three_two = $OUTPUT->blocks('layerthree_two', 'col-md-12');


$tab_one_detail_one = $OUTPUT->blocks('tabone_one', 'col-md-8');
$tab_one_detail_two = $OUTPUT->blocks('tabone_two', 'col-md-4');
$tab_one_detail_three = $OUTPUT->blocks('tabone_three', 'col-md-6 float-left');
$tab_one_detail_four = $OUTPUT->blocks('tabone_four', 'col-md-6 float-right');
$tab_one_detail_five = $OUTPUT->blocks('tabone_five', 'col-md-12');
$tab_one_detail_six = $OUTPUT->blocks('tabone_six', 'col-md-12');

$tab_two_detail_one = $OUTPUT->blocks('tabtwo_one', 'col-md-8');
$tab_two_detail_two = $OUTPUT->blocks('tabtwo_two', 'col-md-4');
$tab_two_detail_three = $OUTPUT->blocks('tabtwo_three', 'col-md-6 float-left');
$tab_two_detail_four = $OUTPUT->blocks('tabtwo_four', 'col-md-6 float-right');
$tab_two_detail_five = $OUTPUT->blocks('tabtwo_five', 'col-md-12');
$tab_two_detail_six = $OUTPUT->blocks('tabtwo_six', 'col-md-12');

$tab_three_detail_one = $OUTPUT->blocks('tabthree_one', 'col-md-8');
$tab_three_detail_two = $OUTPUT->blocks('tabthree_two', 'col-md-4');
$tab_three_detail_three = $OUTPUT->blocks('tabthree_three', 'col-md-6 float-left');
$tab_three_detail_four = $OUTPUT->blocks('tabthree_four', 'col-md-6 float-right');
$tab_three_detail_five = $OUTPUT->blocks('tabthree_five', 'col-md-12');
$tab_three_detail_six = $OUTPUT->blocks('tabthree_six', 'col-md-12');


$buildregionmainsettings = !$PAGE->include_region_main_settings_in_header_actions() && !$PAGE->has_secondary_navigation();
// If the settings menu will be included in the header then don't add it here.
$regionmainsettingsmenu = $buildregionmainsettings ? $OUTPUT->region_main_settings_menu() : false;

$header = $PAGE->activityheader;
$headercontent = $header->export_for_template($renderer);

$templatecontext = [
    'sitename' => format_string($SITE->shortname, true, ['context' => context_course::instance(SITEID), "escape" => false]),
    'output' => $OUTPUT,
    'sidepreblocks' => $blockshtml,
    'hasblocks' => $hasblocks,
    'bodyattributes' => $bodyattributes,
    'courseindexopen' => $courseindexopen,
    'blockdraweropen' => $blockdraweropen,
    'courseindex' => $courseindex,
    'primarymoremenu' => $primarymenu['moremenu'],
    'secondarymoremenu' => $secondarynavigation ?: false,
    'mobileprimarynav' => $primarymenu['mobileprimarynav'],
    'usermenu' => $primarymenu['user'],
    'langmenu' => $primarymenu['lang'],
    'forceblockdraweropen' => $forceblockdraweropen,
    'regionmainsettingsmenu' => $regionmainsettingsmenu,
    'hasregionmainsettingsmenu' => !empty($regionmainsettingsmenu),
    'overflow' => $overflow,
    'leftregion' => $regionleft,
    'rightregion' => $regionright,
    'layerone_detail_full' => $layerone_detail_full,
    'layerone_detail_one' => $layerone_detail_one,
    'layerone_detail_oneone' => $layerone_detail_oneone,
    'layerone_detail_two' => $layerone_detail_two,
    'layertwo_detail_one' => $layertwo_detail_one,
    'layertwo_detail_two' => $layertwo_detail_two,
    'layertwo_detail_three' => $layertwo_detail_three,
    'layertwo_detail_four' => $layertwo_detail_four,
    'layerone_bottom_one' => $layertwo_three_one,
    'layerone_bottom_two' => $layertwo_three_two,
    'headercontent' => $headercontent,
    'addblockbutton' => $addblockbutton,
    

    'tab_one_detail_one' => $tab_one_detail_one,
    'tab_one_detail_two' => $tab_one_detail_two,
    'tab_one_detail_three' => $tab_one_detail_three,
    'tab_one_detail_four' => $tab_one_detail_four,
    'tab_one_detail_five' => $tab_one_detail_five,
    'tab_one_detail_six' => $tab_one_detail_six,


    'tab_two_detail_one' => $tab_two_detail_one,
    'tab_two_detail_two' => $tab_two_detail_two,
    'tab_two_detail_three' => $tab_two_detail_three,
    'tab_two_detail_four' => $tab_two_detail_four,
    'tab_two_detail_five' => $tab_two_detail_five,
    'tab_two_detail_six' => $tab_two_detail_six,


    'tab_three_detail_one' => $tab_three_detail_one,
    'tab_three_detail_two' => $tab_three_detail_two,
    'tab_three_detail_three' => $tab_three_detail_three,
    'tab_three_detail_four' => $tab_three_detail_four,
    'tab_three_detail_five' => $tab_three_detail_five,
    'tab_three_detail_six' => $tab_three_detail_six,

    'tabsview' => $editing,
    'admintab' => $return,
    'admin' => is_siteadmin(),
    'editingteacherrole' => ($DB->record_exists_sql("SELECT u.id FROM {user} u JOIN {role_assignments} ra on u.id=ra.userid 
    JOIN {role} r on r.id=ra.roleid WHERE r.shortname='editingteacher' and u.id=$USER->id")) ? true : false,
    'studentrole' => ($DB->record_exists_sql("SELECT u.id FROM {user} u JOIN {role_assignments} ra on u.id=ra.userid 
    JOIN {role} r on r.id=ra.roleid WHERE r.shortname= 'student' and u.id=$USER->id")) ? true : false,
];
echo $OUTPUT->render_from_template('theme_bloom/dashboard', $templatecontext);
