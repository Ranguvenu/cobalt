/**
 * Add a create new group modal to the page.
 *
 * @module     core_group/AjaxForms
 * @class      AjaxForms
 * @package
 * @copyright  2022 Eabyas Info Solutions <www.eabyas.in>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
define([
    // 'local_curriculum/dataTables.checkboxes',
    'core/str',
    'core/modal_factory',
    'core/modal_events',
    'core/ajax',
    'jquery',
    'jqueryui'
], /*globals modal*/
 function(Str, ModalFactory, ModalEvents, Ajax, $) {
    var curriculum;
    return {
        init: function(args) {
            this.AssignUsers(args);
        },
        curriculumDatatable: function(args) {
            var params = [];
            params.action = 'viewcurriculums';
            params.curriculumstatus = args.curriculumstatus;
            $('#viewcurriculums').dataTable({
                'bInfo': false,
                'processing': true,
                'serverSide': true,
                'ajax': {
                    "type": "POST",
                    "url": M.cfg.wwwroot + '/local/program/ajax.php',
                    "data": params
                },
                "bLengthChange": false,
                "language": {
                    "paginate": {
                        "next": ">",
                        "previous": "<"
                    },
                    'processing': '<img src='+M.cfg.wwwroot + '/local/ajax-loader.svg>'
                },
                "pageLength": 6
            });
        },
        SessionDatatable: function(args) {
        var params = [];
            params.action = 'viewcurriculumsessions';
            params.curriculumid = args.curriculumid;
            params.semesterid = args.semesterid;
            params.bclcid = args.bclcid;
            if(args.action != ''){
                params.tab = args.action;
            } else {
                params.tab = false;
            }

                $('#viewcurriculumsessions').dataTable({
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
                    "url": M.cfg.wwwroot + '/local/program/ajax.php',
                    "data": params
                },
                "responsive": true,
                "pageLength": 5,
                "bLengthChange": false,
                "bInfo" : false,
            });
            this.sessionenrol(args);
        },
        deleteConfirm: function(args) {
            return Str.get_strings([{
                key: 'confirmation',
                component: 'local_curriculum'
            },
            {
                key: 'deleteconfirm',
                component: 'local_curriculum',
                param: args.curriculumname,
            },
            {
                key: 'deleteallconfirm',
                component: 'local_curriculum'
            },
            {
                key: 'Delete'
            },
            {
                key: 'deletecourseconfirm',
                component: 'local_curriculum'
            },
            {
                key: 'cannotdeleteall',
                component: 'local_curriculum'
            },
            {
                key: 'cannotdeletesession',
                component: 'local_curriculum'
            },
            {
                key: 'cannotdeletesemester',
                component: 'local_curriculum'
            },
            {
                key: 'cannotdeletesemesteryear',
                component: 'local_curriculum'
            },
            {
                key: 'confirmunassignfaculty',
                component: 'local_curriculum'
            }
            ]).then(function(s) {
                if (args.action == "deletecurriculum") {
                    s[1] = s[3];
                    var confirm = ModalFactory.types.CONFIRM;
                 } else if (args.action == "deletecurriculumcourse") {
                    s[1] = s[4];
                    var confirm = ModalFactory.types.CONFIRM;
                 } else if (args.action == "cannotdeletecurriculum") {
                    s[1] = s[5];
                    var confirm = ModalFactory.types.DEFAULT;
                 } else if (args.action == "cannotdeletesession") {
                    s[1] = s[6];
                    var confirm = ModalFactory.types.DEFAULT;
                 } else if (args.action == "cannotdeletesemester") {
                    s[1] = s[7];
                    var confirm = ModalFactory.types.DEFAULT;
                 } else if (args.action == "cannotdeletesemesteryear") {
                    s[1] = s[8];
                    var confirm = ModalFactory.types.DEFAULT;
                 } else if (args.action == "unassignfaculty") {
                    s[1] = s[9];
                    var confirm = ModalFactory.types.CONFIRM;
                 } else {
                    s[1] = s[2];
                    var confirm = ModalFactory.types.CONFIRM;
                 }
                ModalFactory.create({
                    title: s[0],
                    type: confirm,
                    body: s[1]
                }).done(function(modal) {
                    this.modal = modal;
                    if(args.action != "cannotdeletecurriculum" && args.action != "cannotdeletesession"
                        && args.action != "cannotdeletesemester" && args.action != "cannotdeletesemesteryear"){
                        modal.setSaveButtonText(s[3]);
                    }
                    modal.getRoot().on(ModalEvents.yes, function(e) {
                        e.preventDefault();
                        args.confirm = true;
                        var promise = Ajax.call([{
                            methodname: 'local_curriculum_' + args.action,
                            args: args
                        }]);
                        promise[0].done(function() {
                            if(args.action == "deleteprogram"){
                                window.location.href = M.cfg.wwwroot + '/local/curriculum/index.php';
                            } else {
                                window.location.href = M.cfg.wwwroot + '/local/curriculum/view.php?ccid=' + args.curriculumid;
                            }
                        }).fail(function() {
                        });
                    }.bind(this));
                    modal.show();
                }.bind(this));
            }.bind(this));
        },
        AssignUsers: function() {
            $('.usersselect').click(function() {
                var type = $(this).data('id');

                if (type === 'removeselect') {
                    $('input#remove').prop('disabled', false);
                    $('input#add').prop('disabled', true);
                } else if (type === 'addselect') {
                    $('input#remove').prop('disabled', true);
                    $('input#add').prop('disabled', false);
                }

                if ($(this).hasClass('select_all')) {
                    $('#' + type + ' option').prop('selected', true);
                } else if ($(this).hasClass('remove_all')) {
                    $('#' + type ).val('').trigger("change");
                }
            });
        },
        curriculumStatus: function(args) {
            return Str.get_strings([
            {
                key: 'confirmation',
                component: 'local_program'
            },
            {
                key: args.actionstatusmsg,
                component: 'local_program'
            },
            {
                key: 'Confirm'
            }]).then(function(s) {
                ModalFactory.create({
                    title: s[0],
                    type: ModalFactory.types.CONFIRM,
                    body: s[1]
                }).done(function(modal) {
                    this.modal = modal;
                    modal.setSaveButtonText(s[2]);
                    modal.getRoot().on(ModalEvents.yes, function(e) {
                        e.preventDefault();
                        args.confirm = true;
                        var promise = Ajax.call([{
                            methodname: 'local_curriculum_' + args.action,
                            args: args
                        }]);
                        promise[0].done(function() {
                            window.location.href = window.location.href+String();
                        }).fail(function() {
                        });
                    }.bind(this));
                    modal.show();
                }.bind(this));
            }.bind(this));
        },
ManagecurriculumStatus: function(args) {
            return Str.get_strings([{
                key: 'confirmation',
                component: 'local_program'
            },
            {
                key: args.actionstatusmsg,
                component: 'local_program',
                param: args.curriculumname,
            },
            {
                key: 'deleteallconfirm',
                component: 'local_program'
            },
            {
                key: 'yes'
            }]).then(function(s) {
                ModalFactory.create({
                    title: s[0],
                    type: ModalFactory.types.CONFIRM,
                    body: s[1]
                }).done(function(modal) {
                    this.modal = modal;
                    modal.setSaveButtonText(s[3]);
                    modal.getRoot().on(ModalEvents.yes, function(e) {
                        e.preventDefault();
                        args.confirm = true;
                        var promise = Ajax.call([{
                            methodname: 'local_program_managecurriculumStatus',
                            args: args
                        }]);
                        promise[0].done(function() {
                            window.location.href = M.cfg.wwwroot + '/local/curriculum/view.php?ccid='+args.curriculumid;
                        }).fail(function() {
                        });
                    }.bind(this));
                    modal.show();
                }.bind(this));
            }.bind(this));
        },
        load: function () {
        },
        unassignCourses: function(args){
            return Str.get_strings([{
                    key: 'confirm'
                },
                {
                    key: 'unassign_courses_confirm',
                    component: 'local_program',
                    param : args
                },
                {
                    key: 'unassign',
                    component:'local_program',
                },
                {
                    key: 'cannotunassign_courses_confirm',
                    component:'local_program',
                }]).then(function(s) {
                    if (args.action == "unassign_course") {
                        s[1] = s[2];
                        var confirm = ModalFactory.types.CONFIRM;
                    } else if (args.action == "cannotunassign_course") {
                        s[1] = s[3];
                        var confirm = ModalFactory.types.DEFAULT;
                    } else {
                         s[1] = s[0];
                        var confirm = ModalFactory.types.CONFIRM;
                    }
                    ModalFactory.create({
                        title: s[0],
                        type: confirm,
                        body: s[1]
                    }).done(function(modal) {
                        this.modal = modal;
                        if (args.action != "cannotunassign_course") {
                            modal.setSaveButtonText(s[2]);
                        }
                        modal.getRoot().on(ModalEvents.yes, function(e) {
                            e.preventDefault();
                           var params = {};
                            params.programid = args.programid;
                            params.curriculumid = args.curriculumid;
                            params.semesterid = args.semesterid;
                            params.yearid = args.yearid;
                            params.courseid = args.courseid;
                            var promise = Ajax.call([{
                                methodname: 'local_program_' + args.action,
                                args: params
                            }]);
                            promise[0].done(function() {
                                window.location.href = window.location.href+String();
                            }).fail(function() {
                            });
                        }.bind(this));
                        modal.show();
                    }.bind(this));
                modal.show();
            }.bind(this));
        },
        ProgramsDatatable: function(args) {
        var params = [];
            params.action = 'viewcurriculumprograms';
            params.type = args.type;
            params.options = args.options;
            var rows_selected = [];
            var table = $('#viewcurriculumprograms').DataTable({
                'processing': true,
                'serverSide': true,
                'ajax': {
                    "type": "POST",
                    "url": M.cfg.wwwroot + '/local/program/ajax.php',
                    "data": params
                },
                "language": {
                    "paginate": {
                        "next": ">",
                        "previous": "<"
                    },
                    'processing': '<img src='+M.cfg.wwwroot + '/local/ajax-loader.svg>'
                },
               'order': [[1, 'asc']],
               'rowCallback': function(row, data){
                  // Get row ID
                  var rowId = data[0];

                  // If row ID is in the list of selected row IDs
                  if($.inArray(rowId, rows_selected) !== -1&&args.type==1){
                     $(row).find('input[type="checkbox"]').prop('checked', true);
                     $(row).addClass('selected');
                  }
               },
                "pageLength": 10
            });
            if(args.type==1){
                // Handle click on checkbox
                $('#viewcurriculumprograms tbody').on('click', '.programcheckbox', function(e){
                   var $row = $(this).closest('tr');
                    //console.log($row);
                   // Get row data
                   var data = table.row($row).data();
                    //console.log(data);
                   // Get row ID
                   var rowId = data[0];

                   // Determine whether row ID is in the list of selected row IDs
                   var index = $.inArray(rowId, rows_selected);

                   // If checkbox is checked and row ID is not in list of selected row IDs
                   if(this.checked && index === -1){
                      rows_selected.push(rowId);

                   // Otherwise, if checkbox is not checked and row ID is in list of selected row IDs
                   } else if (!this.checked && index !== -1){
                      rows_selected.splice(index, 1);
                   }

                   if(this.checked){
                      $row.addClass('selected');
                   } else {
                      $row.removeClass('selected');
                   }

                   // Update state of "Select all" control
                    curriculum.updateDataTableSelectAllCtrl(table);

                   // Prevent click event from propagating to parent
                   e.stopPropagation();
                });

                // Handle click on table cells with checkboxes
                $('#viewcurriculumprograms').on('click', 'tbody td .programcheckbox, thead th .programcheckboxhead', function(){
                   $(this).parent().find('input[type="checkbox"]').trigger('click');
                });

                // Handle click on "Select all" control
                $('thead input[name="select_all"]', table.table().container()).on('click', function(e){
                   if(this.checked){
                      $('#viewcurriculumprograms tbody input[type="checkbox"]:not(:checked)').trigger('click');
                   } else {
                      $('#viewcurriculumprograms tbody input[type="checkbox"]:checked').trigger('click');
                   }

                   // Prevent click event from propagating to parent
                   e.stopPropagation();
                });

                // Handle table draw event
                table.on('draw', function(){
                   // Update state of "Select all" control
                    curriculum.updateDataTableSelectAllCtrl(table);
                });
            }
        },
        updateDataTableSelectAllCtrl:function(table){
                var $table             = table.table().node();
                var $chkbox_all        = $('tbody input[type="checkbox"]', $table);
                var $chkbox_checked    = $('tbody input[type="checkbox"]:checked', $table);
                var chkbox_select_all  = $('thead input[name="select_all"]', $table).get(0);

                // If none of the checkboxes are checked
                if($chkbox_checked.length === 0){
                   chkbox_select_all.checked = false;
                   if('indeterminate' in chkbox_select_all){
                      chkbox_select_all.indeterminate = false;
                   }

                // If all of the checkboxes are checked
                } else if ($chkbox_checked.length === $chkbox_all.length){
                   chkbox_select_all.checked = true;
                   if('indeterminate' in chkbox_select_all){
                      chkbox_select_all.indeterminate = false;
                   }

                // If some of the checkboxes are checked
                } else {
                   chkbox_select_all.checked = true;
                   if('indeterminate' in chkbox_select_all){
                      chkbox_select_all.indeterminate = true;
                   }
                }
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
        masterprogramchildpopup : function(args) {
            $.ajax({
                type: "POST",
                url:   M.cfg.wwwroot + '/local/program/ajax.php',
                data: { curriculumid: args.id, action:'masterprogramchildpopup',
                    sesskey: M.cfg.sesskey
                },
                success: function(returndata) {
                    //Var returned_data is ONLY available inside this fn!
                    ModalFactory.create({
                        title: args.title,
                        body: returndata
                      }).done(function(modal) {
                        // Do what you want with your new modal.

                        modal.show();
                        curriculum.chlidprogramsDatatable(args);
                         modal.setLarge();
                         modal.getRoot().addClass('openLMStransition');
                            modal.getRoot().animate({"right":"0%"}, 500);
                            modal.getRoot().on(ModalEvents.hidden, function() {
                                    modal.destroy();
                            }.bind(this));
                            $(".close").click(function(){
                                modal.hide();
                                modal.destroy();
                            });
                      });
                }
            });
        },
        chlidprogramsDatatable: function(args) {
            var params = [];
            params.action = 'masterprogramchildpopup';
            params.curriculumid = args.id;
            params.stable = 0;
            $('#chlidprograms').dataTable({
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
                    "url": M.cfg.wwwroot + '/local/program/ajax.php',
                    "data":params
                },
                "responsive": true,
                "pageLength": 5,
                "bLengthChange": false,
                "bInfo" : false,
            });
        },

        checkProgramStatus : function(args) {
            $.ajax({
                type: "POST",
                url:   M.cfg.wwwroot + '/local/program/ajax.php',
                data: { programid: args.pid, curriculumid: args.ccid, costcenter: args.costcenter, action:'programstatusvalidation',
                    sesskey: M.cfg.sesskey
                },
                success: function(returndata) {
                    //Var returned_data is ONLY available inside this fn!
                    if(returndata.finalstatus == 'true'){
                        ModalFactory.create({
                        title: "Confirm",
                        type: ModalFactory.types.SAVE_CANCEL,
                        body: Str.get_string('publishprogramconfirmationmessage', 'local_program', args.programname),
                        }).done(function(modal) {
                        this.modal = modal;
                        modal.setSaveButtonText('Confirm');
                        modal.getRoot().on(ModalEvents.yes, function(e) {
                            modal.hide();
                            modal.destroy();
                            e.preventDefault();
                            args.confirm = true;
                            $.ajax({
                            type: "POST",
                            url:   M.cfg.wwwroot + '/local/program/ajax.php',
                            data: { programid: args.pid,curriculumid:args.ccid,costcenter: args.costcenter,action:'publishprogram',
                                sesskey: M.cfg.sesskey
                            },
                            success: function(response){
                                ModalFactory.create({
                                // type: ModalFactory.types.SAVE,
                                title: 'Success!',
                                body: response
                                }).done(function(modal) {
                                    modal.show();
                                    modal.getRoot().on(ModalEvents.hidden, function() {
                                             setTimeout(function(){
                                            modal.destroy();
                                         }, 4000);
                                    }.bind(this));
                                    $(".close").click(function(){
                                        modal.hide();
                                        modal.destroy();
                                    });
                                document.location.reload();
                                // Do what you want with your new modal.
                              });
                            }
                            });
                        }.bind(this));
                    modal.show();
                    }.bind(this));
                    }else{
                    ModalFactory.create({
                        type: ModalFactory.types.CANCEL,
                        title: 'Alert!',
                        body: returndata.message
                      }).done(function(modal) {
                        // Do what you want with your new modal.

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

    };
});
