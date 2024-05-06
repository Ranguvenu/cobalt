/**
* Add a create new group modal to the page.
* @module block_assignments
* @package
* @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
*/
define(['jquery', 'core/ajax', 'local_costcenter/jquery.dataTables'],
	function($) {
		return {
			load : function(){
				$("#tm_sess_tp").DataTable({
					autoWidth: false,
					columnDefs: [{
						targets: ['_all'],
						className: 'mdc-data-table__cell'
					}],
					"oLanguage": {
	                    "sZeroRecords": 'No Session Type Available',
	                    "sSearch": 'search'
	                },
				});
			}
		};
	});
