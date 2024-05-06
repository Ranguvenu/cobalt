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
 * mod_stream
 *
 * @package   mod_stream
 * @copyright eAbyas Info Solutions
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace mod_stream\output;

defined('MOODLE_INTERNAL') || die();

use context_module;

class mobile {

    public static function stream_view($args) {
        global $OUTPUT, $USER, $DB;

        $args = (object) $args;
        $cm = get_coursemodule_from_id('stream', $args->cmid);

        // Capabilities check.
        require_login($args->courseid , false , $cm, true, true);

        $context = context_module::instance($cm->id);

        require_capability ('mod/stream:view', $context);
        if ($args->userid != $USER->id) {
            require_capability('mod/stream:manage', $context);
        }
        $stream = $DB->get_record('stream', array('id' => $cm->instance));

        $stream->name = format_string($stream->name);
        list($stream->intro, $stream->introformat) =
                        external_format_text($stream->intro, $stream->introformat, $context->id,'mod_stream', 'intro');
        $data = array(
            'stream' => $stream,
            'cmid' => $cm->id,
            'courseid' => $args->courseid
        );

        return [
            'templates' => [
                [
                    'id' => 'main',
                    'html' => $OUTPUT->render_from_template('mod_stream/stream', $data),
                ],
            ],
            'javascript' => '',
            'otherdata' => ''
        ];
    }
}