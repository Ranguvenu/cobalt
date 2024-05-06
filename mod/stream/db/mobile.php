<?php
// This file is part of the Certificate module for Moodle - http://moodle.org/
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
 * @package    mod_stream
 * @copyright  eAbyas Info Solutions
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

$addons = [
    'mod_stream' => [ // Plugin identifier
        'handlers' => [ // Different places where the plugin will display content.
            'stream' => [ // Handler unique name (alphanumeric).
                'displaydata' => [
                    'icon' => $CFG->wwwroot . '/mod/stream/pix/icon.png',
                    'class' => '',
                ],

                'delegate' => 'CoreCourseModuleDelegate', // Delegate (where to display the link to the plugin)
                'method' => 'stream_view', // Main function in \mod_stream\output\mobile
            ],
        ],
        'lang' => [ // Language strings that are used in all the handlers.
            ['pluginname', 'stream']
        ],
    ],
];