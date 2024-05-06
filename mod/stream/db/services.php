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
 * URL external functions and service definitions.
 *
 * @package    mod_url
 * @category   external
 * @copyright  2015 Juan Leyva <juan@moodle.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @since      Moodle 3.0
 */

defined('MOODLE_INTERNAL') || die;

$functions = array(

    'mod_stream_view_url' => array(
        'classname'     => 'mod_stream_external',
        'methodname'    => 'view_stream_url',
        'description'   => 'Trigger the course module viewed event and update the module completion status.',
        'type'          => 'write',
        'capabilities'  => 'mod/stream:view',
        'services'      => array(MOODLE_OFFICIAL_MOBILE_SERVICE)
    ),
    'mod_stream_get_stream_in_mobile' => array(
        'classname'     => 'mod_stream_external',
        'methodname'    => 'get_stream_content',
        'description'   => 'Returns a list of streams.',
        'type'          => 'read',
        'services'      => array(MOODLE_OFFICIAL_MOBILE_SERVICE),
    ),

    'mod_streamattempts' => array(
        'classname' => 'mod_stream_external',
        'methodname' => 'mod_streamattempts',
        'description' => 'Inserting streaming data into database',
        'type' => 'write',
        'ajax'        => true,
        'capabilities' => 'mod/stream:write',
        'services'      => array(MOODLE_OFFICIAL_MOBILE_SERVICE),
    ),

    'mod_stream_get_ratings_info' => array(
        'classname'   => 'mod_stream_external',
        'methodname'  => 'get_specific_rating_info',
        'description' => 'specific rating info',
        'type'        => 'read',
        'ajax' => true,
    ),
    'mod_stream_save_comment' => array(
        'classname'   => 'mod_stream_external',
        'methodname'  => 'save_comment',
        'description' => 'save comment',
        'type'        => 'write',
        'services' => array(MOODLE_OFFICIAL_MOBILE_SERVICE),
        'ajax' => true,
    ),
    'mod_stream_display_ratings_content' => array(
        'classname'   => 'mod_stream_external',
        'methodname'  => 'display_ratings_content',
        'description' => 'Display Ratings Content',
        'type'        => 'Read',
        'ajax' => true,
    ),
    'mod_stream_set_module_rating' => array(
        'classname'   => 'mod_stream_external',
        'methodname'  => 'set_module_rating',
        'description' => 'Set Ratings to Modules',
        'type'        => 'Write',
        'ajax' => true,
    ),
    'mod_stream_like_dislike' => array(
        'classname'   => 'mod_stream_external',
        'methodname'  => 'like_dislike',
        'description' => 'Like/Dislike',
        'type'        => 'read/write',
        'services' => array(MOODLE_OFFICIAL_MOBILE_SERVICE),
        'ajax' => true,
    ),
    'mod_stream_get_likedislike' => array(
        'classname'   => 'mod_stream_external',
        'methodname'  => 'get_likedislike',
        'description' => 'Get Likes/Dislikes',
        'type'        => 'read',
        'services' => array(MOODLE_OFFICIAL_MOBILE_SERVICE),
        'ajax' => true,
    ),
    'mod_stream_submit_rating' => array(
        'classname'   => 'mod_stream_external',
        'methodname'  => 'submit_rating',
        'description' => 'Submit Rating',
        'type'        => 'write',
        'services' => array(MOODLE_OFFICIAL_MOBILE_SERVICE),
        'ajax' => true,
    ),
    'mod_stream_get_ratings' => array(
        'classname'   => 'mod_stream_external',
        'methodname'  => 'get_ratings',
        'description' => 'Get Ratings',
        'type'        => 'read',
        'services' => array(MOODLE_OFFICIAL_MOBILE_SERVICE),
        'ajax' => true,
    ),
    'mod_stream_get_reviews' => array(
        'classname'   => 'mod_stream_external',
        'methodname'  => 'get_reviews',
        'description' => 'Get Reviews',
        'type'        => 'read',
        'services' => array(MOODLE_OFFICIAL_MOBILE_SERVICE),
        'ajax' => true,
    ),
    'mod_stream_transfer_stream_report' => array(
        'classname'   => 'mod_stream_external',
        'methodname'  => 'transfer_stream_report',
        'classpath'   => 'mod/stream/classes/external.php',
        'description' => 'Transfer report information',
        'type'        => 'read',
        'ajax' => true,
    ),
    'stream_timeperiod' => array(
        'classname'   => 'mod_stream_external',
        'methodname'  => 'stream_timeperiod',
        'description' => 'Transfer report information',
        'type'        => 'read',
        'services' => array(MOODLE_OFFICIAL_MOBILE_SERVICE),
        'ajax' => true,
    ),
    'mod_stream_completedVideosbyUsersGraph' => array(
        'classname'   => 'mod_stream_external',
        'methodname'  => 'completedVideosbyUsersGraph',
        'description' => 'Transfer report information',
        'classpath'   => 'mod/stream/classes/external.php',
        'type'        => 'read',
        'ajax' => true,
    ),
    'mod_stream_reportsHeader' => array(
        'classname'   => 'mod_stream_external',
        'methodname'  => 'reportsHeader',
        'description' => 'Reports Header information',
        'classpath'   => 'mod/stream/classes/external.php',
        'type'        => 'read',
        'ajax' => true,
    ),
    'mod_stream_reportHeader' => array(
        'classname'   => 'mod_stream_external',
        'methodname'  => 'reportHeader',
        'description' => 'Report Header information',
        'classpath'   => 'mod/stream/classes/external.php',
        'type'        => 'read',
        'ajax' => true,
    ),
    'mod_stream_completedUsersbyVideos' => array(
        'classname'   => 'mod_stream_external',
        'methodname'  => 'completedUsersbyVideos',
        'description' => 'Completed Users by Videos information',
        'classpath'   => 'mod/stream/classes/external.php',
        'type'        => 'read',
        'ajax' => true,
    ),
    'mod_stream_topVideosInfo' => array(
        'classname'   => 'mod_stream_external',
        'methodname'  => 'topVideosInfo',
        'description' => 'Top Videos Info information',
        'classpath'   => 'mod/stream/classes/external.php',
        'type'        => 'read',
        'ajax' => true,
    ),
    'mod_stream_durationbasedinfo' => array(
        'classname'   => 'mod_stream_external',
        'methodname'  => 'durationbasedinfo',
        'description' => 'Duration Based Info information',
        'classpath'   => 'mod/stream/classes/external.php',
        'type'        => 'read',
        'ajax' => true,
    ),
    'mod_stream_tablecontent' => array(
        'classname'   => 'mod_stream_external',
        'methodname'  => 'tablecontent',
        'description' => 'Table content render',
        'classpath'   => 'mod/stream/classes/external.php',
        'type'        => 'read',
        'ajax' => true,
    ),
    'mod_stream_get_streams_by_courses' => array(
        'classname'     => 'mod_stream_external',
        'methodname'    => 'get_streams_by_courses',
        'description'   => 'Returns a list of urls in a provided list of courses, if no list is provided all streams that the user
                            can view will be returned.',
        'type'          => 'read',
        'capabilities'  => 'mod/stream:view',
        'services'      => array(MOODLE_OFFICIAL_MOBILE_SERVICE),
    ),
    'mod_stream_view_stream' => array(
        'classname'     => 'mod_stream_external',
        'methodname'    => 'view_stream',
        'description'   => 'Trigger the course module viewed event and update the module completion status.',
        'type'          => 'write',
        'capabilities'  => 'mod/stream:view',
        'services'      => array(MOODLE_OFFICIAL_MOBILE_SERVICE)
    )
);
