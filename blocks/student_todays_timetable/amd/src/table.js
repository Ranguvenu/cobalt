/**
 * Add a create new group modal to the page.
 *
 * @module     block_student_todays_timetable
 * @package
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
 define(['jquery', 'core/ajax', 'block_student_todays_timetable/jquery.dataTables'],
 function($,) {
    var classes;
    return classes ={
        CustomClassDatatable: function(args) {
            params = [];
            var oTable = $('#block_student_table').DataTable({

                "ordering": true,
                "oLanguage": {
                    "oPaginate": {
                        "sNext": '<i class="fa fa-chevron-right" ></i>',
                        "sPrevious": '<i class="fa fa-chevron-left" ></i>'
                    },
                    "emptyTable": "No sessions available in table"
                }    
             
                });
            }
    };
});