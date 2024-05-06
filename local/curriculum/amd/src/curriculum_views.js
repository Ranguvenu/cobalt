define([
    'local_curriculum/jquery.dataTables',
    'core/str',
    'core/modal_factory',
    'core/modal_events',
    'core/ajax',
    'jquery',
    'jqueryui'
], function(dataTable, Str, ModalFactory, ModalEvents, Ajax, $) {
    return {
        init: function() {
        },
         curriculumdatatable: function(args){
            $('#viewcurriculum').dataTable({
               "searching": true,
                "responsive": true,
                "processing": true,
                "lengthMenu": [[10, 25,50,100, -1], [10,25, 50,100, "All"]],
                "language": {
                    "emptyTable": "No Curriculums available in table",
                    "sProcessing": "<img src= "+ M.cfg.wwwroot + "/local/ajax-loader.svg />"
                },
                 "aaSorting": [],
                 "pageLength": 10,
            "sAjaxSource": M.cfg.wwwroot + "/local/curriculum/manual_curriculum_processing.php?param="+args,
            });
        },
        UsersDatatable: function() {
        var params = [];
            params.action = 'viewcurriculumusers';
            params.curriculumid = $('#viewcurriculumusers').data('curriculumid');
            params.yearid = $('#viewcurriculumusers').data('yearid');
            $('#viewcurriculumusers').dataTable({
                'processing': true,
                'serverSide': true,
                "language": {
                    "paginate": {
                    "next": ">",
                    "previous": "<"
                    },
                    "processing": '<img src='+M.cfg.wwwroot + '/local/ajax-loader.svg>',
                    "search": "",
                    "searchPlaceholder": "Search"
                },
                'ajax': {
                    "type": "POST",
                    "url": M.cfg.wwwroot + '/local/curriculum/ajax.php',
                    "data":params
                },
                "responsive": true,
                "pageLength": 10,
            });
        },
        checkProgramStatus : function(args) {
               $.ajax({
                    type: "POST",
                    url:   M.cfg.wwwroot + '/local/curriculum/ajax.php',
                    data: { id: args.id, curriculumid: args.ccid, costcenter: args.costcenter, action:'programstatusvalidation',
                        sesskey: M.cfg.sesskey
                          },
                    success: function(returndata) {
                        if(returndata.finalstatus == 'true'){
                            ModalFactory.create({
                             title: 'Confirmation',
                             type: ModalFactory.types.SAVE_CANCEL,
                             body: Str.get_string('publishcurriculum_message', 'local_curriculum'),
                            }).done(function(modal) {
                                this.modal = modal;
                                modal.setSaveButtonText('Publish');
                                modal.getRoot().on(ModalEvents.save, function() {
                                var returndata = "Published Successfully....";
                                ModalFactory.create({
                                title: 'Success!',
                                body : returndata,
                                }).done(function(modal) {
                                    modal.show();
                                    setTimeout(function(){
                                                modal.destroy();
                                             },20000);
                                    document.location.reload();
                                 }.bind(this));
                                }.bind(this));
                             modal.show();
                            }.bind(this));
                        }else{
                            ModalFactory.create({
                                type: ModalFactory.types.CANCEL,
                                title: 'Alert!',
                                body: returndata.message
                              }).done(function(modal){
                                    modal.show();
                                    modal.getRoot().on(ModalEvents.hidden, function() {
                                            modal.destroy();
                                    }.bind(this));
                                    $(".close").click(function(){
                                        modal.hide();
                                        modal.destroy();
                                    });
                              });
                        }
                    }
               });
        },
         FacultysDatatable: function() {
        var params = [];
            params.action = 'viewcoursefaculty';
            params.yearid = $('#viewcoursefaculty').data('yearid');
            params.semesterid = $('#viewcoursefaculty').data('semesterid');
            params.courseid = $('#viewcoursefaculty').data('courseid');
            $('#viewcoursefaculty').dataTable({
                'processing': true,
                'serverSide': true,
                "language": {
                    "paginate": {
                    "next": ">",
                    "previous": "<"
                    },
                    "processing": '<img src='+M.cfg.wwwroot + '/local/ajax-loader.svg>',
                    "search": "",
                    "searchPlaceholder": "Search"
                },
                'ajax': {
                    "type": "POST",
                    "url": M.cfg.wwwroot + '/local/curriculum/ajax.php',
                    "data":params
                },
                "responsive": true,
                "pageLength": 5,
                "bLengthChange": false,
                "bInfo" : false,
            });
        },
    };
});
