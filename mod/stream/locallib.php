<?php

// This file is part of Moodle - http://moodle.org/
////
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


defined('MOODLE_INTERNAL') || die;

require_once("$CFG->libdir/filelib.php");
require_once("$CFG->libdir/resourcelib.php");
require_once("$CFG->dirroot/mod/stream/lib.php");

/**
 * This methods does weak url validation, we are looking for major problems only,
 * no strict RFE validation.
 *
 * @param $url
 * @return bool true is seems valid, false if definitely not valid URL
 */
function stream_appears_valid_url($url) {
    if (preg_match('/^(\/|https?:|ftp:)/i', $url)) {
        // note: this is not exact validation, we look for severely malformed URLs only
        return (bool)preg_match('/^[a-z]+:\/\/([^:@\s]+:[^@\s]+@)?[a-z0-9_\.\-]+(:[0-9]+)?(\/[^#]*)?(#.*)?$/i', $url);
    } else {
        return (bool)preg_match('/^[a-z]+:\/\/...*$/i', $url);
    }
}

/**
 * Fix common URL problems that we want teachers to see fixed
 * the next time they edit the resource.
 *
 * This function does not include any XSS protection.
 *
 * @param string $url
 * @return string
 */
function stream_fix_submitted_url($url) {
    // note: empty urls are prevented in form validation
    $url = trim($url);

    // remove encoded entities - we want the raw URI here
    $url = html_entity_decode($url, ENT_QUOTES, 'UTF-8');

    if (!preg_match('|^[a-z]+:|i', $url) and !preg_match('|^/|', $url)) {
        // invalid URI, try to fix it by making it normal URL,
        // please note relative urls are not allowed, /xx/yy links are ok
        $url = 'http://'.$url;
    }
    return $url;
}

/**
 * Decide the best display format.
 * @param object $url
 * @return int display type constant
 */
function stream_get_final_display_type($url) {
    global $CFG;

    if ($url->display != RESOURCELIB_DISPLAY_AUTO) {
        return $url->display;
    }

    // detect links to local moodle pages
    if (strpos($url->externalurl, $CFG->wwwroot) === 0) {
        if (strpos($url->externalurl, 'file.php') === false and strpos($url->externalurl, '.php') !== false ) {
            // most probably our moodle page with navigation
            return RESOURCELIB_DISPLAY_OPEN;
        }
    }

    static $download = array('application/zip', 'application/x-tar', 'application/g-zip',     // binary formats
                             'application/pdf', 'text/html');  // these are known to cause trouble for external links, sorry
    static $embed    = array('image/gif', 'image/jpeg', 'image/png', 'image/svg+xml',         // images
                             'application/x-shockwave-flash', 'video/x-flv', 'video/x-ms-wm', // video formats
                             'video/quicktime', 'video/mpeg', 'video/mp4',
                             'audio/mp3', 'audio/x-realaudio-plugin', 'x-realaudio-plugin',   // audio formats,
                            );

    $mimetype = resourcelib_guess_url_mimetype($url->externalurl);

    if (in_array($mimetype, $download)) {
        return RESOURCELIB_DISPLAY_DOWNLOAD;
    }
    if (in_array($mimetype, $embed)) {
        return RESOURCELIB_DISPLAY_EMBED;
    }

    // let the browser deal with it somehow
    return RESOURCELIB_DISPLAY_OPEN;
}


/**
 * Optimised mimetype detection from general URL
 * @param $fullurl
 * @param int $size of the icon.
 * @return string|null mimetype or null when the filetype is not relevant.
 */
function stream_guess_icon($fullurl, $size = null) {
    global $CFG;
    require_once("$CFG->libdir/filelib.php");

    if (substr_count($fullurl, '/') < 3 or substr($fullurl, -1) === '/') {
        // Most probably default directory - index.php, index.html, etc. Return null because
        // we want to use the default module icon instead of the HTML file icon.
        return null;
    }

    $icon = file_extension_icon($fullurl, $size);
    $htmlicon = file_extension_icon('.htm', $size);
    $unknownicon = file_extension_icon('', $size);

    // We do not want to return those icon types, the module icon is more appropriate.
    if ($icon === $unknownicon || $icon === $htmlicon) {
        return null;
    }

    return $icon;
}
function stream_reports( $courseid, $activityid) {
    global $CFG, $DB, $USER;
    $reportBuilder = new \mod_stream\reports();

    $dailyvisits = $reportBuilder->dailyVisitsData($activityid);

    $ratingsdata = $reportBuilder->ratingsInfoReport($activityid);

    $totalviews = $reportBuilder->totalviewssql($courseid, $activityid);

    $timeperiods = $reportBuilder->timeperiod($courseid);

    $totalactive = $reportBuilder->totalactive($courseid);
    extract($totalactive);

    $videosbyusersdata = $reportBuilder->videos_usersdata($courseid);
    extract($videosbyusersdata);

    $likesinfo = $reportBuilder->likesInfoReport(null, $activityid);
    extract($likesinfo);

    $like_dislike_served = $reportBuilder->like_dislike_served($courseid, $activityid);
    extract($like_dislike_served);

    $likesbasedduration = $reportBuilder->likes_based_duration($courseid);
    extract($likesbasedduration);

    $top_like_rate_view = $reportBuilder->top_like_rate_view($courseid);
    extract($top_like_rate_view);

    $week_statictics = $reportBuilder->get_week_statistics($courseid, $activityid);

    extract($week_statictics);


    return array('totalviews' => $totalviews, 
                 'dailyvisits' => json_encode(array_values($dailyvisits), JSON_NUMERIC_CHECK),
                 'total' => $total, 
                 'active' => $active, 
                 'totalviews' => $totalviews, 
                 'completedvideosbyusers' => json_encode(array_values($completedvideosbyusers), JSON_NUMERIC_CHECK), 
                 'completedusersbyvideos' => json_encode(array_values($completedusersbyvideos), JSON_NUMERIC_CHECK), 
                 'totallikesdata' => $totallikesdata, 
                 'totaldislikes' => $totaldislikes, 
                 'servedtime' => $servedtime, 
                 'toplikes' => json_encode(array_values($toplikes), JSON_NUMERIC_CHECK), 
                 'topratings' => json_encode(array_values($topratings), JSON_NUMERIC_CHECK), 
                 'topviews' => json_encode(array_values($topviews), JSON_NUMERIC_CHECK), 
                 'timeperiods' => array_values($timeperiods), 
                 'likesinfo' => $likesdata, 
                 'dislikesinfo' => $dislikesdata, 
                 'ratingsinfo' => $ratingsdata,
                 'weekvideos' => json_encode(array_values($weekvideos), JSON_NUMERIC_CHECK), 
                 'weekminutes' => json_encode(array_values($weekminutes), JSON_NUMERIC_CHECK), 
                 'weekviews' => json_encode(array_values($weekviews), JSON_NUMERIC_CHECK), 
                 'weeklikesdislikes' => json_encode(array_values($weeklikesdislikes), JSON_NUMERIC_CHECK),
                 'weekratings' => json_encode(array_values($weekratings), JSON_NUMERIC_CHECK)

    );
}