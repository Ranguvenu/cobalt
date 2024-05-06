/**
 * Add a create new group modal to the page.
 *
 * @module     local/Program
 * @class      Program
 * @package
 * @copyright  2022 eAbyas Info Solutions
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
define([
    'local_program/jquery.dataTables',
    'core/str',
    'core/modal_factory',
    'core/modal_events',
    'core/ajax',
    'jquery',
    'jqueryui',
    'core/notification'
], function(dataTable, Str, ModalFactory, ModalEvents, Ajax, $, Notification) {
    return {
        init: function(args) {
            this.AssignUsers(args);
        },
        programDatatable: function(args) {
            var params = [];
            params.action = 'viewprograms';
            params.programstatus = args.programstatus;
            Str.get_string('search','local_program').then(function(s) {
            $('#viewprograms').dataTable({
                'processing': true,
                'serverSide': true,
                'ajax': {
                    "type": "POST",
                    "url": M.cfg.wwwroot + '/local/program/ajax.php?subdepts='
                    +args.selected_subdepts+'&costcenterid='
                    +args.selectedcostcenterid+'&departmentid='
                    +args.selecteddepartmentid+'&program='
                    +args.selectedprogram+'&status='
                    +args.selectedstatus+'&groups='+args.selectedgroups,
                    "data": params
                },
                "bInfo" : false,
                "bLengthChange": false,
                "language": {
                    "paginate": {
                        "next": ">",
                        "previous": "<"
                    },
                    'processing': '<img src='+M.cfg.wwwroot + '/local/ajax-loader.svg>'
                },
                 "oLanguage": {
                    "sSearch": s,
                    "sZeroRecords": 'No Programs Available',
                 },
                "pageLength": 6
            });
            });
        },
        SessionDatatable: function(args) {
            var params = [];
            params.action = 'viewprogramsessions';
            params.programid = args.programid;
            params.levelid = args.levelid;
            params.bclcid = args.bclcid;
            if(args.action != ''){
                params.tab = args.action;
            } else {
                params.tab = false;
            }
            $('#viewprogramsessions').dataTable({
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
        sessionenrol: function(args) {
            $(document).on('click', '.sessionenrol', function() {
                var sessionargs = $(this).data();
                return Str.get_strings([{
                    key: 'confirmation',
                    component: 'local_program'
                },
                {
                    key: 'confirmschedulesession',
                    component: 'local_program'
                },
                {
                    key: 'confirmreschedulesession',
                    component: 'local_program'
                },
                {
                    key: 'confirmcancelsession',
                    component: 'local_program'
                },
                {
                    key: 'yes'
                }
                ]).then(function(s) {
                    var body = s[1];
                    if (sessionargs.enrol == 2) {
                        body = s[2];
                    } else if (sessionargs.enrol == 3) {
                        body = s[3];
                    }
                    ModalFactory.create({
                        title: s[0],
                        type: ModalFactory.types.SAVE_CANCEL,
                        body: body
                    }).done(function(modal) {
                        this.modal = modal;
                        modal.setSaveButtonText(s[4]);
                        modal.getRoot().on(ModalEvents.save, function(e) {
                            e.preventDefault();
                            args.confirm = true;
                            var promise = Ajax.call([{
                                methodname: 'local_program_session_enrolments',
                                args: sessionargs
                            }]);
                            promise[0].done(function() {
                                window.location.href = window.location.href + String();
                            }).fail(function() {
                                // do something with the exception
                            });
                        }.bind(this));
                        modal.show();
                    }.bind(this));
                }.bind(this));
            });
        },
        CoursesDatatable: function() {
            var params = [];
            params.action = 'viewprogramcourses';
            params.programid = $('#viewprogramcourses').data('programid');
            $('#viewprogramcourses').dataTable({
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
        UsersDatatable: function() {
            var params = [];
            params.action = 'viewprogramusers';
            params.programid = $('#viewprogramusers').data('programid');
            $('#viewprogramusers').dataTable({
                'processing': true,
                'serverSide': true,
                "language": {
                    "paginate": {
                    "next": ">",
                    "previous": "<"
                    },
                    "processing": '<img src='+M.cfg.wwwroot + '/local/ajax-loader.svg>',
                    "search": "",
                    "searchPlaceholder": "Search",
                    "sZeroRecords": "No Students Available",
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
        GroupsDatatable: function() {
            var params = [];
            params.action = 'viewgroupusers';

            params.programid = $('#viewgroupusers').data('programid');
            $('#viewgroupusers').dataTable({
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
        GroupsDatatables: function() {
            var params = [];
            params.action = 'viewgroupusersdata';
            params.cohortid = $('#viewgroupusersdata').data('groupid');
            $('#viewgroupusersdata').dataTable({
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
        deleteConfirm: function(args) {
            return Str.get_strings([{
                key: 'confirmation',
                component: 'local_program'
            },
            {
                key: 'no'
            },
            {
                key: 'deleteallconfirm',
                component: 'local_program'
            },
            {
                key: 'delete'
            },
            {
                key: 'deletecourseconfirm',
                component: 'local_program'
            },
            {
                key: 'cannotdeleteall',
                component: 'local_program'
            },
            {
                key: 'cannotdeletesession',
                component: 'local_program'
            },
            {
                key: 'cannotdeletelevel',
                component: 'local_program'
            },
            {
                key: 'inactiveconfirm',
                component: 'local_program',
                param: args.programname,
            },
            {
                key: 'activeconfirm',
                component: 'local_program',
                param: args.programname,
            },
            {
                key: 'deleteconfirm',
                component: 'local_program',
                param: args.programname,
            },
            ]).then(function(s) {
                if (args.action == "deleteprogram") {
                    s[1] = s[10];
                    var confirm = ModalFactory.types.SAVE_CANCEL;
                 } else if (args.action == "deleteprogramcourse") {
                    s[1] = s[4];
                    var confirm = ModalFactory.types.SAVE_CANCEL;
                 } else if (args.action == "cannotdeleteprogram") {
                    s[1] = s[5];
                    var confirm = ModalFactory.types.DEFAULT;
                 } else if (args.action == "cannotdeletesession") {
                    s[1] = s[6];
                    var confirm = ModalFactory.types.DEFAULT;
                 } else if (args.action == "cannotdeletelevel") {
                    s[1] = s[7];
                    var confirm = ModalFactory.types.DEFAULT;
                 }else if (args.action == "inactiveprogram") {
                    s[1] = s[8];
                    var confirm = ModalFactory.types.SAVE_CANCEL;
                 }else if (args.action == "activeprogram") {
                    s[1] = s[9];
                    var confirm = ModalFactory.types.SAVE_CANCEL;
                 } else {
                    s[1] = s[2];
                    var confirm = ModalFactory.types.SAVE_CANCEL;
                 }
                ModalFactory.create({
                    title: s[0],
                    type: confirm,
                    body: s[1]
                }).done(function(modal) {
                    this.modal = modal;
                    if(args.action != "cannotdeleteprogram" &&
                        args.action != "cannotdeletesession" &&
                        args.action != "cannotdeletelevel"){
                        modal.setSaveButtonText(s[3]);
                    }
                    if(args.action === "cannotdeletelevel"){
                        $(document).on('click', '#page-local-program-view .modal-header .close', function() {
                            modal.destroy();
                        });
                    }
                    if (args.action === "deletelevel") {
                        $(document).on('click', '#page-local-program-view .modal-footer .btn-secondary', function() {
                            modal.destroy();
                        });
                        $(document).on('click', '#page-local-program-view .modal-header .close', function() {
                            modal.destroy();
                        });
                    }
                    modal.getRoot().on(ModalEvents.save, function(e) {
                        e.preventDefault();
                        args.confirm = true;
                        var promise = Ajax.call([{
                            methodname: 'local_program_' + args.action,
                            args: args
                        }]);
                        promise[0].done(function() {
                            if(args.action == "deletestream"){
                                window.location.href = M.cfg.wwwroot + '/local/program/streams.php';
                            }else if(args.action == "deletesession" ||
                                args.action == "deleteprogram" ||
                                args.action == "activeprogram" ||
                                args.action == "inactiveprogram"){
                                window.location.href = window.location.href + String();
                            } else {
                                window.location.href = M.cfg.wwwroot +'/local/program/view.php?bcid=' + args.programid;
                            }
                        }).fail(function() {
                            // do something with the exception
                            console.log(args);
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
        programStatus: function(args) {
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
                key: 'yes'
            }]).then(function(s) {
                ModalFactory.create({
                    title: s[0],
                    type: ModalFactory.types.SAVE_CANCEL,
                    body: s[1]
                }).done(function(modal) {
                    this.modal = modal;
                    modal.setSaveButtonText(s[2]);
                    modal.getRoot().on(ModalEvents.save, function(e) {
                        e.preventDefault();
                        args.confirm = true;
                        var promise = Ajax.call([{
                            methodname: 'local_program_' + args.action,
                            args: args
                        }]);
                        promise[0].done(function() {
                            window.location.href = window.location.href + String();
                        }).fail(function() {
                            // do something with the exception
                        });
                    }.bind(this));
                    modal.show();
                }.bind(this));
            }.bind(this));
        },
    ManageprogramStatus: function(args) {
            return Str.get_strings([{
                key: 'confirmation',
                component: 'local_program'
            },
            {
                key: args.actionstatusmsg,
                component: 'local_program',
                param: args.programname,
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
                    type: ModalFactory.types.SAVE_CANCEL,
                    body: s[1]
                }).done(function(modal) {
                    this.modal = modal;
                    modal.setSaveButtonText(s[3]);
                    modal.getRoot().on(ModalEvents.save, function(e) {
                        e.preventDefault();
                        args.confirm = true;
                        var promise = Ajax.call([{
                            methodname: 'local_program_manageprogramStatus',
                            args: args
                        }]);
                        promise[0].done(function() {
                            window.location.href = M.cfg.wwwroot + '/local/program/view.php?bcid='+args.programid;
                        }).fail(function() {
                            // do something with the exception
                        });
                    }.bind(this));
                    modal.show();
                }.bind(this));
            }.bind(this));
        },
        load: function () {
                $(document).on('click', '#id_institute_type_1, #id_institute_type_2', function(){
                    $('#fitem_id_instituteid .form-autocomplete-selection .badge.badge-info').trigger('click');
                    $('#fitem_id_roomid .form-autocomplete-selection .badge.badge-info').trigger('click');
                });
        },
        unassignCourses: function(args){
            return Str.get_strings([{
                    key: 'confirm'
                },
                {
                    key: 'yes'
                },
                {
                    key: 'unassign',
                    component:'local_program',
                },
                {
                    key: 'cannotunassign_courses_confirm',
                    component:'local_program',
                },
                {
                    key: 'unassign_courses_confirm',
                    component: 'local_program',
                    param : args
                }]).then(function(s) {
                    if (args.action == "unassign_course") {
                        s[1] = s[4];
                        var confirm = ModalFactory.types.SAVE_CANCEL;
                    } else if (args.action == "cannotunassign_course") {
                        s[1] = s[3];
                        var confirm = ModalFactory.types.DEFAULT;
                    } else {
                         s[1] = s[4];
                        var confirm = ModalFactory.types.SAVE_CANCEL;
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
                        if(args.action === "cannotunassign_course"){
                            $(document).on('click', '#page-local-program-view .modal-header .close', function() {
                                modal.destroy();
                            });
                        }
                        if (args.action === "unassign_course") {
                            $(document).on('click', '#page-local-program-view .modal-content .modal-footer .btn-secondary', function() {
                                modal.destroy();
                            });
                            $(document).on('click', '#page-local-program-view .modal-content .modal-header .close', function() {
                                modal.destroy();
                            });
                        }
                        modal.getRoot().on(ModalEvents.save, function(e) {
                            e.preventDefault();
                            var params = {};
                            params.programid = args.bcid;
                            params.levelid = args.levelid;
                            params.bclcid = args.bclcid;
                            var promise = Ajax.call([{
                                methodname: 'local_program_' + args.action,
                                args: params
                            }]);
                            promise[0].done(function() {
                                modal.destroy();
                                if (args.action === "unassign_course") {
                                    $.ajax({
                                        method: 'POST',
                                        url: M.cfg.wwwroot + '/local/program/ajax.php',
                                        data: {
                                            action: 'programlevelcourses',
                                            programid:args.bcid,
                                            levelid: args.levelid
                                        },
                                        success:function(resp){
                                            $('.levetabscontent_container').html(resp);
                                        }
                                    });
                                }
                                // window.location.href = window.location.href + String();
                            }).fail(function() {
                                // do something with the exception
                            });
                        }.bind(this));
                        modal.show();
                    }.bind(this));
                this.modal.show();
            }.bind(this));
        },
        /** ODL-866: IKRAM CODE STARTS HERE-- **/
        enroluser:function(args){
            return Str.get_strings([
                    {key: 'course_enrolment', component: 'local_program'},
                    {key: 'course_enrolment_confirm',component: 'local_program', param:args.coursename},
                    {key: 'yes'},
                    {key: 'no'}
                ]).then(function(s) {
                    if (args.action == "enrol") {
                        var confirm = ModalFactory.types.SAVE_CANCEL;
                    } 
                    ModalFactory.create({
                        title: s[0],
                        type: confirm,
                        body: s[1]
                    }).done(function(modal) {
                        this.modal = modal;
                        modal.getRoot().on(ModalEvents.save, function(e) {
                            e.preventDefault();
                            var params = {};
                            params.programid = args.programid;
                            params.userid = args.userid;
                            params.courseid = args.courseid;
                            params.levelid = args.levelid;
                            var promise = Ajax.call([{
                                methodname: 'local_program_assign_course',
                                args: params
                            }]);
                            promise[0].done(function() {
                                window.location.href = window.location.href+'?bcid=' + args.programid
                            }).fail(function (exception) {
                                // body...
                            });
                        }.bind(this));
                        modal.show();
                    }.bind(this));
                this.modal.show();
            }.bind(this));
        },
        StreamsDatatable: function() {
            var params = [];
            params.action = 'viewprogramstreams';
            Str.get_string('search','local_program').then(function(s) {
            $('#viewprogramstreams').dataTable({
                'processing': true,
                'serverSide': true,
                'ajax': {
                    "type": "POST",
                    "url": M.cfg.wwwroot + '/local/program/ajax.php',
                    "data": params
                },
                "bInfo" : false,
                "bLengthChange": false,
                "language": {
                    "paginate": {
                        "next": ">",
                        "previous": "<"
                    },
                    'processing': '<img src='+M.cfg.wwwroot + '/local/ajax-loader.svg>'
                },
                "oLanguage": {
                    "sSearch": s
                 },
                "pageLength": 6
            });
            });
        },
        getstream: function() {
            $(document).on('change', '#id_costcenter', function(){
                var orgID = $(this).val();
                if(orgID){
                    var promise = Ajax.call([{
                        methodname: 'local_program_streams',
                        args: {
                            orgid: orgID,
                        },
                    }]);
                    promise[0].done(function(resp) {
                        var template;
                        var customstrings = Str.get_strings(
                            [{
                                key: 'selectstrem',
                                component: 'local_program'
                            }]);
                            return customstrings.then(function(strings) {
                                template = '<option value=null>'+strings[0]+'</option>';
                            });
                            $.each(JSON.parse(resp), function( index, value) {
                                template += '<option value = ' + index + ' >' +value + '</option>';
                            });
                            $('#id_stream').html(template);
                    }).fail(function() {
                        // do something with the exception
                        alert('Error occured while processing request');
                         window.location.reload();
                    });
                } else {
                    var template;
                    var customstrings = Str.get_strings(
                            [{
                                key: 'selectstrem',
                                component: 'local_program'
                            }]);
                    return customstrings.then(function(strings) {
                        template = '<option value=\'\'>'+strings[0]+'</option>';
                    });
                    $('#id_stream').html(template);
                }
           });
        },
        unEnrolUser : function(args){
            return Str.get_strings([{
                key: 'confirm'
            },
            {
                key: 'program_self_unenrolment',
                component: 'local_program',
                param :args.programname
            }]).then(function(s) {
                ModalFactory.create({
                    title: s[0],
                    type: ModalFactory.types.SAVE_CANCEL,
                    body: s[1]
                }).done(function(modal) {
                    this.modal = modal;
                    modal.setSaveButtonText(s[0]);
                    modal.getRoot().on(ModalEvents.save, function(e) {
                        e.preventDefault();
                        var params = {};
                        params.userid = args.userid;
                        params.programid = args.programid;
                        params.contextid = args.contextid;
                        var promise = Ajax.call([{
                            methodname: 'local_program_unenrol_user',
                            args: params
                        }]);
                        promise[0].done(function() {
                            window.location.href = M.cfg.wwwroot;
                        }).fail(function() {
                        });
                    }.bind(this));
                    modal.show();
                }.bind(this));
            }.bind(this));
        },
    };
});
