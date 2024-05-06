define(['block_proposals/jquery.dataTables', 'jquery', 'core/str', 'core/modal_factory', 'core/modal_events', 'core/fragment', 'core/ajax', 'core/yui', 'core/templates'],
    function(dataTable, $, Str, ModalFactory, ModalEvents, Fragment, Ajax, Y, Templates) {
        var classes;
        return classes = {
            init: function(args) {
            },
            CustomClassDatatable: function(args) {
                params = [];
                var oTable = $('#table , #table2 , #table3 , #table4').DataTable({
                    "searching": true,
                    "responsive": true,
                    "processing": true,
                    "lengthMenu": [[10, 25,50,100, -1], [10,25, 50,100, "All"]],
                    "language": {
                        "emptyTable": "No forms available in table",
                        "paginate": {
                            "previous": "<",
                            "next": ">"
                        },
                    },
                })
            }
        }
    });
