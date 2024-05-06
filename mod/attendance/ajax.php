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

require_once('../../config.php');

require_login();
global $DB, $OUTPUT, $PAGE, $USER;
$PAGE->set_url('/mod/attendance/ajax.php');
$PAGE->set_pagelayout('incourse');

// Print the header.
$strplural = get_string("modulename", "attendance");
$PAGE->set_title($strplural);
echo $OUTPUT->header();
$ratedval = $_REQUEST['ratedInd'];
$ratedby = $_REQUEST['ratedby'];
$sessionid = $_REQUEST['sessionid'];
$instructorid = $_REQUEST['instructorid'];

$ratings = new \stdClass();
$ratings->instructorid = $instructorid;
$ratings->sessionid = $sessionid;
$ratings->ratedby = $ratedby;
$ratings->rating = $ratedval;
$ratings->timecreated = time();

$ratingsgiven = $DB->get_record_sql("SELECT id, rating, sessionid FROM {ratings} WHERE ratedby = $ratedby AND sessionid = $sessionid AND instructorid = $instructorid");
if ($ratingsgiven->sessionid == $sessionid) {
	$ratingsgiven->rating = $ratedval;
	$ratingsgiven->timemodified = time();
	$updaterate = $DB->update_record('ratings', $ratingsgiven);
}else{
	$insertrate = $DB->insert_record('ratings', $ratings);
}
echo $OUTPUT->footer();
