<?php

global $DB;
$id = optional_param('id', 0, PARAM_INT);
// $semid = $DB->get_field('local_timeintervals', 'id', ['semesterid' => $id]);
$slotcount = $DB->count_records('local_timeintervals_slots', ['timeintervalid' => $id]);

$mform->addElement('header', 'settingsheader', get_string('settime_intervals', 'local_timetable'));

$style = array();

if (isset($classid)) {
    if (isset($this->_customdata['use_section'])) {
        $display = true;
    }
    $existing = class_existing_sections($classid);
}

if (isset($existing) && $existing != false) {
    $existing = get_string('existing_sections', 'local_class', implode(', ', $existing));
    $mform->addElement('static', 'existing_sections', '', $existing);
}


for ($i = 0; $i <= 24; $i++) {
    $hours[$i] = sprintf("%02d", $i);
}
for ($i = 0; $i < 60; $i+=5) {
    $minutes[$i] = sprintf("%02d", $i);
}

$sec = array();

$sec[] = & $mform->createElement('static', 'from_label', '', '<b>' . get_string('from', 'local_timetable') . ' : </b>');
$sec[] = & $mform->createElement('select', 'starthours', get_string('hour', 'form'), $hours, false, true);
$sec[] = & $mform->createElement('select', 'startminutes', get_string('minute', 'form'), $minutes, false, true);
// $sec[] = & $mform->createElement('select', 'start_td', get_string('minute', 'form'), array('am' => 'AM', 'pm' => 'PM'), false, true);
$sec[] = & $mform->createElement('static', 'section_space', '', '&nbsp;&nbsp;&nbsp;');
$sec[] = & $mform->createElement('static', 'to_label', '', '<b>' . get_string('to', 'local_timetable') . ' : </b>');
$sec[] = & $mform->createElement('select', 'endhours', get_string('hour', 'form'), $hours, false, true);
$sec[] = & $mform->createElement('select', 'endminutes', get_string('minute', 'form'), $minutes, false, true);
// $sec[] = & $mform->createElement('select', 'end_td', get_string('minute', 'form'), array('am' => 'AM', 'pm' => 'PM'), false, true);
$sec[] = & $mform->createElement('hidden', 'rid');
$mform->setDefault('rid', 0);
$mform->setType('rid', PARAM_INT);

if ($id > 0) {
    $sec[] = & $mform->createElement('hidden', 'sectionid');
}

$repeatarray = array();
$repeatarray[] = $mform->createElement('group', 'section_array', '', $sec, array(' '), false);

if ($id > 0) {
    $default = $slotcount;
} else {
    $default = 3;
}
if (isset($sectionid)) {
    $default = 1;
}

$repeateloptions = array('');
$repeateloptions['name']['disabledif'] = array('use_section', 'eq', 0);
$repeateloptions['sectionlimit']['disabledif'] = array('use_section', 'eq', 0);
$repeateloptions['from_label'][1]['name'] = 'sdsadfsad';

$mform->setType('name', PARAM_CLEANHTML);
$mform->setType('sectionlimit', PARAM_RAW);
$mform->setType('sectionid', PARAM_INT);

$this->repeat_elements($repeatarray, $default, $repeateloptions, 'section_repeats', 'option_add_fields', 1, get_string('addtimeintervals', 'local_timetable'), true);
