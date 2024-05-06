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
 * @module     local_admissions/semestertabs
 * @package
 * @copyright  2022 eAbyas info solutions
 * @license    <http://www.gnu.org/copyleft/gpl.html> GNU GPL v3 or later
 */
define(['jquery', 'core/templates'],
    function($, Templates) {
        return {
        load: function (){
        },
       dataArguments:function (args) {
            var levelid = args.levelid;
            var programid = args.programid;
            var container = $('#myTabContent_'+programid);
            $.ajax({
               url: M.cfg.wwwroot+'/local/admissions/ajax.php',
               method: 'POST',
               dataType: 'json',
               data: {levelid : levelid, programid : programid},
               success: function (res) {
                    var templateresponse = Templates.render('local_admissions/semestertabcontent', res);
                    templateresponse.then(function(html, js){
                        Templates.replaceNodeContents(container, html, js);
                    });
               }
            });
        },
        semTabsCollection:function (args) {
            var levelid = args.levelid;
            var programid = args.programid;
            var containerid = $('#levelCourses_'+programid);
            $.ajax({
               url: M.cfg.wwwroot+'/local/admissions/ajax.php',
               method: 'POST',
               dataType: 'json',
               data: {levelid : levelid, programid : programid},
               success: function (res) {
                    var templateresponse = Templates.render('local_admissions/levelcontent', res);
                    templateresponse.then(function(html, js){
                        Templates.replaceNodeContents(containerid, html, js);
                    });
               }
            });
        }
    };
});

