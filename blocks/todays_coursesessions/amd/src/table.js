/**
 * Add a create new group modal to the page.
 *
 * @module     block_todays_coursesessions
 * @package
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
 define(['jquery', 'core/ajax', 'block_todays_coursesessions/jquery.dataTables'],
 function($,) {
return {
 load : function(){
    $("#todays_coursesessions").DataTable({
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