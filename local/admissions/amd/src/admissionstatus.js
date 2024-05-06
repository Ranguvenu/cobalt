/**
 * Add a create new group modal to the page.
 *
 * @module     local_admissions/confirm
 * @class      admissionstatus
 * @package
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
// 'jquery', 'core/modal_factory', 'core/str', 'core/modal_events', 'core/ajax', 'core/notification'
define([
    'jquery',
    'local_program/jquery.dataTables',
    'core/str',
    'core/modal_factory',
    'core/modal_events',
    'core/ajax',
    'jqueryui'
 ], function($, dataTable, Str, ModalFactory, ModalEvents, Ajax) {
    return {
        init: function(args) {
            this.AssignUsers(args);
        },
 acceptConfirm: function(args) {
            return Str.get_strings([{
                key: 'acceptapplication',
                component: 'local_admissions'
            },
            {
                key: 'save_continue',
                component: 'local_admissions'
            },
            {
                key: 'acceptconfirm',
                component: 'local_admissions'
            },
            {
                key: 'save_continue',
                component: 'local_admissions'
            },
            {
                key: 'deletecourseconfirm',
                component: 'local_admissions'
            },
            {
                key: 'cannotdeleteall',
                component: 'local_admissions'
            },
            {
                key: 'cannotdeletesession',
                component: 'local_admissions'
            },
            {
                key: 'cannotdeletelevel',
                component: 'local_admissions'
            },
            {
                key: 'inactiveconfirm',
                component: 'local_admissions',
                param: args.programname,
            },
            {
                key: 'activeconfirm',
                component: 'local_admissions',
                param: args.programname,
            },
            {
                key: 'acceptconfirm',
                component: 'local_admissions',
                param: args.programname,
            },
            {
                key: 'rejectconfirm',
                component: 'local_admissions'
            },
            ]).then(function(s) {
                if (args.action == "acceptconfirm") {
                    s[1] = s[10];
                    var confirm = ModalFactory.types.SAVE_CANCEL;
                 } else if (args.action == "rejectadmission") {
                    s[1] = s[11];
                    var confirm = ModalFactory.types.SAVE_CANCEL;
                 } else if (args.action == "acceptconfirm") {
                    s[1] = s[4];
                    var confirm = ModalFactory.types.SAVE_CANCEL;
                 } else if (args.action == "cannotdeletelevel") {
                    s[1] = s[5];
                    var confirm = ModalFactory.types.DEFAULT;
                 } else if (args.action == "cannotdeletesession") {
                    s[1] = s[6];
                    var confirm = ModalFactory.types.DEFAULT;
                 } else if (args.action == "cannotdeletelevel") {
                    s[1] = s[7];
                    var confirm = ModalFactory.types.DEFAULT;
                 }else if (args.action == "inactiveconfirm") {
                    s[1] = s[8];
                    var confirm = ModalFactory.types.SAVE;
                 }else if (args.action == "activeconfirm") {
                    s[1] = s[9];
                    var confirm = ModalFactory.types.SAVE_CANCEL;
                 } else {
                    s[1] = s[2];
                    var confirm = ModalFactory.types.SAVE_CANCEL;
                 }
                ModalFactory.create({
                    title: s[0],
                    type: confirm,
                    body: s[2]
                }).done(function(modal) {
                    this.modal = modal;
                    if(args.action != "cannotdeleteprogram" &&
                        args.action != "cannotdeletesession" &&
                        args.action != "cannotdeletelevel"){
                        modal.setSaveButtonText(s[3]);
                    }
                    modal.getRoot().on(ModalEvents.save, function(e) {
                        e.preventDefault();
                        args.confirm = true;
                        var params = {};
                        params.programid = args.programid;
                        if (args.action == "rejectadmission") {
                            args.action = "reject_admission";
                            params.userid = args.userid;
                            params.programid = args.programid;
                        }
                        if (args.action == "acceptadmission") {
                            args.action = "accept_admission";
                            params.action = args.action;
                            params.context = args.context;
                            params.id = args.id;
                            params.programid = args.programid;
                            params.confirm = args.confirm;
                            params.programname = args.programname;
                        }
                        // let footer = Y.one('.modal-footer');
                        let footer = Y.one('.modal-footer');
                        // footer.setContent(String.get_string('deleting', 'block_queries'));
                        let spinner = M.util.add_spinner(Y, footer);
                        spinner.show();
                        var promise = Ajax.call([{
                            methodname: 'local_admissions_' + args.action,
                            args: params
                        }]);
                        promise[0].done(function() {
                            if(args.action == "deletestream"){
                                window.location.href = M.cfg.wwwroot + '/local/admissions/view.php';
                            }else if(args.action == "deletesession" ||
                                args.action == "acceptconfirm" ||
                                args.action == "activeconfirm" ||
                                args.action == "inactiveconfirm"){
                                modal.hide();
                                window.location.reload();
                            } else {
                                modal.hide();
                                window.location.reload();
                            }
                        }).fail(function() {
                            // do something with the exception
                        });
                    }.bind(this));
                    modal.show();
                }.bind(this));
            }.bind(this));
        },
    };
 });
