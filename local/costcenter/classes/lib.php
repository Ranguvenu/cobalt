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
 * local local_costcenter
 *
 * @package    local_costcenter
 * @copyright  2022 eAbyas <eAbyas.in>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
namespace local_costcenter;
class lib {
    public static function get_userdate($format, $timestamp = null, $timezone = 99, $fixday = false, $fixhour = true) {

        $formatidentifiers = array('d', 'm', 'y', 'j', 'D', 'M', 'Y', 'H', 'i', 's', 'a', 'A', 'G', 'F', 'g', 'h');
        $strftimeformatidentifiers = array('%d', '%m', '%y', '%e',  '%D', '%b', '%Y', '%H',
                                           '%M', '%S', '%P', '%p', '%k', '%B', '%l', '%I'
                                        );
        foreach ($formatidentifiers as $key => $identifier) {
            $format = str_replace($identifier, $strftimeformatidentifiers[$key], $format);
        }
        if (is_null($timestamp)) {
            $timestamp = time();
        }
        return userdate($timestamp, $format, $timezone, $fixday, $fixhour);
    }
    public static function get_mail_userdate($user, $format, $timestamp = null, $timezone = 99, $fixday = true, $fixhour = true) {
        $formatidentifiers = array('d', 'm', 'y', 'j', 'D', 'M', 'Y', 'H', 'i', 's', 'a', 'A', 'G', 'F', 'g', 'h');
        $strftimeformatidentifiers = array('%d', '%m', '%y', '%e',  '%D', '%b', '%Y', '%H',
                                           '%M', '%S', '%P', '%p', '%k', '%B', '%l', '%I'
                                        );
        foreach ($formatidentifiers as $key => $identifier) {
            $format = str_replace($identifier, $strftimeformatidentifiers[$key], $format);
        }
        if (is_null($timestamp)) {
             $timestamp = time();
        }
        return userdate($timestamp, $format, $timezone, $fixday, $fixhour);
    }
    public static function strip_tags_custom($content) {
        return mb_convert_encoding(clean_text(html_to_text($content)), 'UTF-8');
    }
}
