$(document).ready(function() {
                        $("#block_previousss_timetable , #block_todays_timetable_addnewsessions , #block_todays_timetables").dataTable({
                        "searching": true,
                        "responsive": true,
                        "processing": true,
                        "lengthMenu": [[10, 25,50,100, -1], [10,25, 50,100, "All"]],
                        "language": {
                            "emptyTable": "No records In Table",
                            "paginate": {
                                "previous": "<",
                                "next": ">"
                            },
                        },
                         "aaSorting": [],
                         "pageLength": 10,
                        });
                        });
