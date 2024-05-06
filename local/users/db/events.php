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
 * @package   local
 * @subpackage  users
 * @author eabyas  <info@eabyas.in>
**/


//// List of observers.
$observers = array(

    array(
        'eventname'   => '\core\event\user_profile_viewed',
        'callback'    => 'local_users_observer::user_profile_viewed',
    ),
    array(
        'eventname'   => '\core\event\user_loggedin',
        'callback'    => 'local_users_observer::user_loggedin',
    ),

);