define(['block_hod/jquery.dataTables', 'jquery'],               
  function(DataTable, $) {
    
       var classes;
        return classes ={
            init: function(args) { 
        },
        CustomClassDatatable: function(args) {
            params = [];
            alert('hello');
            var oTable = $('#table , #table2 , #table3 , #table4').DataTable({
                "searching": true,
                "responsive": true,
                "processing": true,
                "lengthMenu": [[10, 25,50,100, -1], [10,25, 50,100, "All"]],
                  "language": {
                    "emptyTable": "No Forms available in table",
                    "paginate": {
                        "previous": "<",
                        "next": ">"
                    },
                },
                })
            }
        }


});             
