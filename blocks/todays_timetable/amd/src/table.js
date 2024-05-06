// $(document).ready(function() {
//     $('#tab').DataTable();
//     // alert('HELLO');
// });

/**
 * Add a create new group modal to the page.
 *
 * @module     block_todays_timetable
 * @package
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
 define(['jquery', 'core/ajax', 'block_todays_timetable/jquery.dataTables'],
 function($,) {
return {
 load : function(){
    $("#block_todays_timetables").DataTable({
       autoWidth: false,
        columnDefs: [
            {
                targets: ['_all'],
                className: 'mdc-data-table__cell'
            }
          ]
     });
 }
};
});