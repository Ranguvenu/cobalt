/**
 * @module     block_queries
 * @package
 * @copyright  2022
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
 define(['block_queries/datatables', 'jquery'], function(dataTable, $) {
    return {
        queriedatatable: function() {
            $('#queryresponse').DataTable({
                'sort' : false,
                'language': {
                    'paginate': {
                       'next': '>',
                       'previous': '<'
                    },
                    'processing': '<img src=' + M.cfg.wwwroot + '/pix/y/loading.gif>'
                },
                "oLanguage": {
                    "sZeroRecords": 'No Queries Available',
                }
            });
        }
    };
});
