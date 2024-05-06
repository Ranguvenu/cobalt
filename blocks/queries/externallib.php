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
 * @subpackage block_queries
 */

defined('MOODLE_INTERNAL') || die();
global $PAGE, $OUTPUT;

use block_queries\blocks\queries as queries;
use core_user;

require_once("$CFG->libdir/externallib.php");
class blocks_queries_external extends external_api {

    /**
     * Delete Querie record.
     * @param int $queryid.
     * @return queryid .
     */
    public static function delete_query_parameters() {
        return new external_function_parameters(
            ['responseid' => new external_value(PARAM_INT, 'responseid')],
        );
    }
    public static function delete_query($responseid) {
        global $DB;

        $DB->delete_records('block_query_response', ['id' => $responseid]);
    }
    public static function delete_query_returns() {
            return new external_value(PARAM_INT, "Query deleted successfully");
    }

    /**
     * Delete responce Querie record.
     * @param int $queryid.
     * @return queryid .
     */
    public static function delete_responce_parameters() {
        return new external_function_parameters(
            ['queryid' => new external_value(PARAM_INT, 'queryid')],
        );
    }
    public static function delete_responce($queryid) {
        global $DB;

        $DB->delete_records('block_queries', ['id' => $queryid]);

    }
    public static function delete_responce_returns() {
        return new external_value(PARAM_INT, "Deleted responce query successfully");
    }
}

