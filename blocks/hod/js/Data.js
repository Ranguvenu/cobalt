$(document).ready(function() {
                        $("#table3 , #table2 , #table , #table4").dataTable({
                        "searching": true,
                        "responsive": true,
                        "processing": true,
                        "lengthMenu": [[10, 25,50,100, -1], [10,25, 50,100, "All"]],
                        "language": {
                            "emptyTable": "No records in the table",
                            "paginate": {
                                "previous": "<",
                                "next": ">"
                            },
                        },
                         "aaSorting": [],
                         "pageLength": 10,
                        });
                        });
