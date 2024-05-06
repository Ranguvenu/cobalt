/**
 * Add a create new group modal to the page.
 *
 * @module     local_employees/newemployees
 * @class      NewUser
 * @package
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
define(['jquery', 'core/str', 'core/modal_factory', 'core/modal_events',
    'core/fragment', 'core/ajax', 'core/yui','local_courses/jquery.dataTables'],
        function($, Str, ModalFactory, ModalEvents, Fragment, Ajax, Y) {

    /**
     * Constructor
     *
     * @param {String} args used to find triggers for the new group modal.
     * Each call to init gets it's own instance of this class.
     */
    var Newemployees = function(args) {

        this.contextid = args.context;
        this.id = args.id;
        var self = this;
        this.args = args;
        self.init(args);
    };

    /**
     * @var {Modal} modal
     * @private
     */
    Newemployees.prototype.modal = null;

    /**
     * @var {int} contextid
     * @private
     */
    Newemployees.prototype.contextid = -1;

    /**
     * Initialise the class.
     * @private
     * @return {Promise}
     */
    Newemployees.prototype.init = function() {
        var self = this;
        // Fetch the title string.
            if (self.id) {
                var strings  = Str.get_strings([
                                {
                                    key: 'editemployees',
                                    component: 'local_employees'
                                },
                                {
                                    key: 'save_continue',
                                    component: 'local_employees'
                                },                                
                                {
                                    key: 'previous',
                                    component: 'local_employees'
                                },
                                {
                                    key: 'cancel',
                                    component: 'local_employees'
                                }]);
            }else{
               var strings  = Str.get_strings([
                            {
                                key: 'adnewemployees',
                                component: 'local_employees'
                            },
                            {
                                key: 'save_continue',
                                component: 'local_employees'
                            },
                            {
                                key: 'previous',
                                component: 'local_employees'
                            },
                            {
                                key: 'cancel',
                                component: 'local_employees'
                            }]);
            }

            return strings.then(function(strings) {
                // Create the modal.
                return ModalFactory.create({
                    type: ModalFactory.types.DEFAULT,
                    title: strings[0],
                    body: this.getBody(),
                    footer: this.getFooter(strings),
                });
            }.bind(this)).then(function(modal) {
                // Keep a reference to the modal.
                this.modal = modal;
                // Forms are big, we want a big modal.
                this.modal.setLarge();

                this.modal.getRoot().addClass('openLMStransition local_employees');

                this.modal.getRoot().on(ModalEvents.hidden, function() {
                    this.modal.getRoot().animate({"right":"-85%"}, 500);
                    setTimeout(function(){
                        modal.destroy();
                    }, 500);
                }.bind(this));

                this.modal.getFooter().find('[data-action="save"]').on('click', this.submitForm.bind(this));
                // We also catch the form submit event and use it to submit the form with ajax.

                this.modal.getFooter().find('[data-action="cancel"]').on('click', function() {
                    modal.hide();
                    setTimeout(function(){
                        modal.destroy();
                    }, 500);
                    window.location.reload();
                });

                this.modal.getFooter().find('[data-action="previous"]').on('click', function() {
                    self.args.form_status = self.args.form_status - 1;
                    var data = self.getBody();
                    data.then(function(html) {
                        if(html === false) {
                            window.location.reload();
                        }
                    });
                    modal.setBody(data);
                    if(self.args.form_status==0){
                        $('[data-action="previous"]').css('display', 'none');
                    }else{
                        $('[data-action="previous"]').css('display', 'block');
                    }
                });

                this.modal.getRoot().on('submit', 'form', function(form) {
                    self.submitFormAjax(form, self.args);
                });
                this.modal.show();
                this.modal.getRoot().animate({"right":"0%"}, 500);

                return this.modal;
            }.bind(this));

    };

    /**
     * @param {String} formdata
     * @method getBody
     * @private
     * @return {Promise}
     */
    Newemployees.prototype.getBody = function(formdata) {
        if (typeof formdata === "undefined") {
            formdata = {};
        }
        this.args.jsonformdata = JSON.stringify(formdata);
        return Fragment.loadFragment('local_employees', 'new_create_employees', this.contextid, this.args);
    };
    /**
     * @param {String} strings used to find triggers for the new group modal.
     * @method getFooter
     * @private
     * @return {Promise}
     */
    Newemployees.prototype.getFooter = function(strings) {
        var $footer;
        var $style;
        $footer = '<button type="button" class="btn btn-primary" data-action="save">'+ strings[1] +'</button>&nbsp;';
        $style = 'style="display:none;"';
$footer += '<button type="button" class="btn btn-secondary" data-action="previous" ' + $style + ' >'+ strings[2] +'</button>&nbsp;';
$footer += '<button type="button" class="btn btn-secondary" data-action="cancel">'+ strings[3] +'</button>';
        return $footer;
    };

    /**
     * @method handleFormSubmissionResponse
     * @private
     * @return {Promise}
     */
    Newemployees.prototype.handleFormSubmissionResponse = function() {
        this.modal.hide();
        // We could trigger an event instead.
        // Yuk.
        Y.use('moodle-core-formchangechecker', function() {
            M.core_formchangechecker.reset_form_dirty_state();
        });
        // This will be the context for our template. So {{name}} in the template will resolve to "Tweety bird".
        var Templates;
        var args;
        var context = { id: args.id};

                var modalPromise = ModalFactory.create({
                    type: ModalFactory.types.DEFAULT,
                    body: Templates.render('local_classroom/classroomview', context),
                });
                $.when(modalPromise).then(function() {
                }).fail(Notification.exception);
    };

    /**
     * @param {object} data
     * @method handleFormSubmissionFailure
     * @private
     * @return {Promise}
     */
    Newemployees.prototype.handleFormSubmissionFailure = function(data) {
        // Oh noes! Epic fail :(
        // Ah wait - this is normal. We need to re-display the form with errors!
        this.modal.setBody(this.getBody(data));
    };

    /**
     * Private method
     * @method submitFormAjax
     * @private
     * @param {Event} e Form submission event.
     * @param {object} args
     */
    Newemployees.prototype.submitFormAjax = function(e ,args) {
        // We don't want to do a real form submission.
        e.preventDefault();
        var self = this;
        // Convert all the form elements values to a serialised string.
        var formData = this.modal.getRoot().find('form').serialize();
        var methodname = 'local_employees_submit_creates_employees_form';
        var params = {};
        params.id = 1;
        params.contextid = this.contextid;
        params.jsonformdata = JSON.stringify(formData);
        params.form_status = args.form_status;

        var promise = Ajax.call([{
            methodname: methodname,
            args: params
        }]);

         promise[0].done(function(resp){
            if(resp.form_status !== -1 && resp.form_status !== false) {
                self.args.form_status = resp.form_status;
                self.args.id = resp.id;
                self.handleFormSubmissionFailure();
            } else {
                self.modal.hide();
                window.location.reload();
            }
            if(args.form_status > 0) {
                $('[data-action="previous"]').css('display', 'inline-block');
            }

        }).fail(function(){
            self.handleFormSubmissionFailure(formData);
        });

    };

    /**
     * This triggers a form submission, so that any mform elements can do final tricks before the form submission is processed.
     *
     * @method submitForm
     * @param {Event} e Form submission event.
     * @private
     */
    Newemployees.prototype.submitForm = function(e) {
        e.preventDefault();
        var self = this;
        self.modal.getRoot().find('form').submit();
    };

    return /** @alias module:local_employees/newemployees */ {
        // Public variables and functions.
        /**
         * Attach event listeners to initialise this module.
         *
         * @method init
         * @param {string} args The CSS selector used to find nodes that will trigger this module.
         * @return {Promise}
         */
        init: function(args) {
            return new Newemployees(args);
        },
        load: function(){
            $(document).on('change', '#id_open_costcenterid', function() {
              var costcentervalue = $(this).find("option:selected").val();
               if (costcentervalue !== null) {
                    var params = {};
                    params.costcenterid = costcentervalue;
                    params.contextid = 1;
                    var promise = Ajax.call([{
                        methodname: 'local_employees_get_departments_list',
                        args: params
                    }]);
                    promise[0].done(function(resp) {
                        var resp = JSON.parse(resp);
                        var template = '';
                        $.each(resp, function(index,value) {
                            template += '<option value = ' + index + ' >' +value + '</option>';
                        });
                        $("#id_open_departmentid").html(template);
                    });
                }
                $('#id_open_departmentid').trigger('change');
                $('#id_open_subdepartment').trigger('change');
            });
            $(document).on('change', '#id_open_departmentid', function() {
              var departmentvalue = $(this).find("option:selected").val();
               if (departmentvalue !== null) {
                    var params = {};
                    params.departmentid = departmentvalue;
                    params.contextid = 1;
                    var promise = Ajax.call([{
                        methodname: 'local_employees_get_subdepartments_list',
                        args: params
                    }]);
                    promise[0].done(function(resp) {
                        var resp = JSON.parse(resp);
                        var template = '';
                        $.each(resp, function(index,value) {
                            template += '<option value = ' + index + ' >' +value + '</option>';
                        });
                        $("#id_open_subdepartment").html(template);
                    });
                }
            });
            $(document).on('change', '#id_open_costcenterid', function() {
                var costcentervalue = $(this).find("option:selected").val();
                if (costcentervalue != 0) {
                    var params = {};
                    params.costcenterid = costcentervalue;
                    params.contextid = 1;
                    var promise = Ajax.call([{
                        methodname: 'local_employees_get_supervisors_list',
                        args: params
                    }]);
                    promise[0].done(function(resp) {
                        var resp = JSON.parse(resp);
                        var template = '';
                        $.each(resp, function(index,value) {
                            template += '<option value = ' + index + ' >' +value + '</option>';
                        });
                        $("#open_supervisorid").html(template);
                    });
                }
                $('#open_supervisorid').trigger('change');
            });
        },
        trashConfirm: function(args) {
            return Str.get_strings([{
                key: 'confirm'
            },
            {
                key: 'trashconfirm',
                component: 'local_employees',
                param :args
            },
            {
                key: 'trashallconfirm',
                component: 'local_employees'
            },
            {
                key: 'trash'
            }]).then(function(s) {
        ModalFactory.create({
            title: s[0],
            type: ModalFactory.types.DEFAULT,
            body: s[1],
            footer: '<button type="button" class="btn btn-secondary" data-action="cancel">Cancel</button>&nbsp;' +
            '<button type="button" class="btn btn-primary" data-action="save">Delete</button>'
        }).done(function(modal) {
            this.modal = modal;
            modal.getRoot().find('[data-action="save"]').on('click', function() {
                args.confirm = true;
                var promise = Ajax.call([{
                    methodname: 'local_employees_' + args.action,
                    args: {
                        id: args.id,
                    },
                }]);
                promise[0].done(function() {
                    window.location.reload();
                    window.location.href = window.location.href+String();
                }).fail(function() {
                });
            }.bind(this));
            modal.getFooter().find('[data-action="cancel"]').on('click', function() {
                modal.setBody('');
                modal.hide();
            });
            modal.show();
        }.bind(this));
    }.bind(this));
},
   deleteteacherConfirm: function(args) {
            return Str.get_strings([{
                key: 'confirm'
            },
            {
                key: 'delete_teacher',
                component: 'local_employees',
                param :args
            }
            ]).then(function(s) {
                ModalFactory.create({
                    title: s[0],
                    // type: ModalFactory.types.SAVE_CANCEL,
                    body: s[1]
                }).done(function(modal) {
                    this.modal = modal;
                    // modal.setSaveButtonText(s[3]);
                    modal.getRoot().on(ModalEvents.save, function(e) {
                        e.preventDefault();
                        args.confirm = true;
                        var params = {};
                        params.id = args.id;
                        params.contextid = args.contextid;
                        var promise = Ajax.call([{
                            methodname: 'local_employees_'+args.action,
                            args: params
                        }]);
                        promise[0].done(function() {
                            window.location.href = window.location.href+String();
                        }).fail(function() {
                            // do something with the exception
                        });
                    }.bind(this));
                    modal.show();
                }.bind(this));
            }.bind(this));
        },
        teachernothide: function(args) {
            console.log(args.fullname);
            return Str.get_strings([{
                key: 'confirm'
            },
            {
                key: 'cannothide',
                component: 'local_employees',
                param : args.fullname
            }
            ]).then(function(s) {
                ModalFactory.create({
                    title: s[0],
                    type: ModalFactory.types.DEFAULT,
                    body: s[1]
                }).done(function(modal) {
                    this.modal = modal;
                    modal.show();
                }.bind(this));
            }.bind(this));
        },

        employeesSuspend: function(args) {
            return Str.get_strings([{
                key: 'confirm'
            },
            {
                key: 'suspendconfirm'+args.status,
                component: 'local_employees',
                param :args
            },
            {
                key: 'suspendallconfirm',
                component: 'local_employees'
            },
            {
                key: 'confirm'
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
                        var params = {};
                        params.id = args.id;
                        params.contextid = args.contextid;

                        var promise = Ajax.call([{
                            methodname: 'local_employees_suspend_employees',
                            args: params
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
    };
});
