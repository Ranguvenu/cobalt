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
 * @package stream
 * @subpackage block_stream
 */
defined('MOODLE_INTERNAL') || die;

$functions = array(
	'block_stream_tablecontent' => array(
        'classname'   => 'block_stream_external',
        'methodname'  => 'tablecontent',
        'description' => 'Table content render',
        'classpath'   => 'blocks/stream/classes/external.php',
        'type'        => 'read',
        'ajax' => true,
    ),
    'block_stream_upload_video' => array(
        'classname'   => 'block_stream_external',
        'methodname'  => 'upload_video',
        'description' => 'Upload video',
        'classpath'   => 'blocks/stream/classes/external.php',
        'type'        => 'write',
        'ajax' => true,
    ),
    'block_stream_delete_video' => array(
        'classname'   => 'block_stream_external',
        'methodname'  => 'delete_video',
        'description' => 'Delete uploaded video',
        'classpath'   => 'blocks/stream/classes/external.php',
        'type'        => 'write',
        'ajax' => true,
    ),
    'block_stream_update_video_stream' => array(
        'classname'   => 'block_stream_external',
        'methodname'  => 'update_video',
        'description' => 'Update uploaded video',
        'classpath'   => 'blocks/stream/classes/external.php',
        'type'        => 'write',
        'ajax' => true,
    ),
);