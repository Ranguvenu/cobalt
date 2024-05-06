/**
 * Add a create new group modal to the page.
 *
 * @module     block_teacher_courses
 * @package
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
 define(['jquery', 'core/ajax', 'block_students_attendance/jquery.dataTables'],
 function($,) {
return {
 load : function(){
    $("#teacher1_courses_table2").DataTable({
       autoWidth: false,
        columnDefs: [
            {
                targets: ['_all'],
                className: 'mdc-data-table__cell',
                "aLengthMenu": [ 3, 10, 25, 50, 100 ],
            }
          ]
     });
 }
};
});