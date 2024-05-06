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
 * Add a create new group modal to the page.
 *
 * @module     local_program/programcompletion
 * @class      programcompletion
 * @package
 * @copyright  2022 eAbyas info solutions
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
define(['jquery'],
    function($) {
        return {
        load: function (){

        },
       saveToTheDB:function (args) {
            var chekboxval = args.indx;
            var prgmid = args.programid;
            var usrid = args.userid;
            $.ajax({
               url: M.cfg.wwwroot+'/local/program/request.php',
               method: 'POST',
               dataType: 'json',
               data: {chekboxval:chekboxval, prgmid:prgmid, usrid:usrid},
               success: function (r) {
                    chekboxval = r.id;
                    localStorage.setItem('chekboxval', chekboxval);
               }
            });
        }
    };
});
