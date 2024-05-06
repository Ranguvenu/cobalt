/**
 * Add a create new group modal to the page.
 *
 * @module     core_group/AjaxForms
 * @class      AjaxForms
 * @package
 * @copyright  2022 eAbyas Info Solutions
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
define(['jquery',
        'core/str',
        'core/modal_factory',
        'core/modal_events',
        'core/fragment',
        'core/ajax',
        'core/yui',
        'core/templates',],
        function($, Str, ModalFactory, ModalEvents, Fragment, Ajax, Y, Templates) {

    var AjaxForms = function(args) {
        this.contextid = args.contextid;
        this.args = args;
        this.init(args);
    };
    /**
     * @var {Modal} modal
     * @private
     */
    AjaxForms.prototype.modal = null;
    /**
     * @var {int} contextid
     * @private
     */
    AjaxForms.prototype.contextid = -1;
    /**
     * Initialise the class.
     *
     * @param {object} args used to find triggers for the new group modal.
     * @private
     * @return {Promise}
     */
    AjaxForms.prototype.init = function(args) {
        // Fetch the title string.
        var header_label;
        var self = this;
            switch (args.callback) {
                case 'session_form':
                    switch (args.action) {
                        case 1:
                            header_label = {key:'addsession', component:'local_timetable'};
                        break;
                        case 2:
                            header_label = {key:'updatesession', component:'local_timetable'};
                        break;
                    }
                break;
                case 'update_session':
                    switch (args.action) {
                        case 1:
                            header_label = {key:'addsession', component:'local_timetable'};
                        break;
                        case 2:
                            header_label = {key:'updatesession', component:'local_timetable'};
                        break;
                    }
                break;
            }
        var customstrings = Str.get_strings([header_label,{
                        key: 'savecontinue',
                        component: 'local_timetable'
                    },
                    {
                        key: 'previous',
                        component: 'local_timetable'
                    },
                    {
                        key: 'cancel',
                        component: 'local_timetable'
                    },
                    {
                        key: 'save',
                        component: 'local_timetable'
                    }]);

        return customstrings.then(function(strings) {
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

            this.modal.getRoot().addClass('openLMStransition local_timetable');

            // We want to reset the form every time it is opened.
            this.modal.getRoot().on(ModalEvents.hidden, function() {
                this.modal.getRoot().animate({"right":"-85%"}, 500);
                setTimeout(function(){
                    modal.destroy();
                }, 1000);
            }.bind(this));
            this.modal.getFooter().find('[data-action="save"]').on('click', this.submitForm.bind(this));
            this.modal.getFooter().find('[data-action="cancel"]').on('click', function() {
                modal.destroy();
                window.location.href =  window.location.href + String();
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
            $(".close").click(function(){
                window.location.href =  window.location.href + String();
            });
            return this.modal;
        }.bind(this));
    };
    /**
     * @method getBody
     * @private
     * @param {object} formdata
     * @return {Promise}
     */
    AjaxForms.prototype.getBody = function(formdata) {
        if (typeof formdata === "undefined") {
            formdata = {};
        }
        // Get the content of the modal.
        this.args.jsonformdata = JSON.stringify(formdata);
        return Fragment.loadFragment(this.args.component, this.args.callback, this.contextid, this.args);
    };
    /**
     * @method getFooter
     * @private
     * @param {object} customstrings
     * @return {Promise}
     */
    AjaxForms.prototype.getFooter = function(strings) {
        var footer;
        var style;
        footer = '<button type="button" class="btn btn-primary" data-action="save">'+ strings[4] +'</button>&nbsp;';
        style = 'style="display:none;"';
        footer += '<button type="button" class="btn btn-secondary" data-action="previous" ' + style + ' >'+ strings[2] +'</button>&nbsp;';
        footer += '<button type="button" class="btn btn-secondary" data-action="cancel">'+ strings[3] +'</button>';
        return footer;
    };
    /**
     * @method handleFormSubmissionResponse
     * @private
     * @param {object} args
     * @return {Promise}
     */
    AjaxForms.prototype.handleFormSubmissionResponse = function(args) {
        this.modal.destroy();
        // We could trigger an event instead.
        // Yuk.
        Y.use('moodle-core-formchangechecker', function() {
            M.core_formchangechecker.reset_form_dirty_state();
        });
        window.location.reload();
    };
    /**
     * @method handleFormSubmissionFailure
     * @private
     * @param {object} data
     * @return {Promise}
     */
    AjaxForms.prototype.handleFormSubmissionFailure = function(data) {        
        // Oh noes! Epic fail :(
        // Ah wait - this is normal. We need to re-display the form with errors!
        this.modal.setBody(this.getBody(data));
    };

    /**
     * Private method
     *
     * @method submitFormAjax
     * @private
     * @param {Event} e Form submission event.
     * @param {object} args
     */
    AjaxForms.prototype.submitFormAjax = function(e, args) {
        // We don't want to do a real form submission.
        /*var coursesname = data[6]['name'];
        var courseid = data[6]['value'];*/
        e.preventDefault();
        var self = this;
        // Convert all the form elements values to a serialised string.
        var formData = this.modal.getRoot().find('form').serialize();
        var data = this.modal.getRoot().find('form').serializeArray();
        if (args.callback === "update_session") {
            var method = 'local_timetable_update_instance';
        }
        if (args.callback === "session_form") {
            var method = 'local_timetable_submit_instance';
        }

        var methodname = method;
        // Now we can continue...
        var params = {};
        if (args.callback === "update_session") {
            params.id = args.id;
        }
        if (args.callback === "session_form" && args.id > 0) {
            params.id = args.id;
        }
        params.contextid = this.contextid;
        params.jsonformdata = JSON.stringify(formData);
        params.form_status = args.form_status;
        params.dayname = args.dayname;
        params.semesterid = args.semesterid;
        params.slotid = args.slotid;
        var promise = Ajax.call([{
            methodname: methodname,
            args: params
        }]);
        promise[0].done(function(resp){
            if (resp.form_status == null) {
                self.args.id = resp.id;
                self.handleFormSubmissionResponse(self.args);
            }
            if (resp.form_status !== -1 && resp.form_status !== false) {
                self.args.form_status = resp.form_status;
                self.args.id = resp.id;
                self.handleFormSubmissionFailure(self.args);
            } else {
                self.args.id = resp.id;
                self.handleFormSubmissionResponse(self.args);
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
    AjaxForms.prototype.submitForm = function(e) {
        e.preventDefault();
        this.modal.getRoot().find('form').submit();
    };
    return /** @alias module:core_group/AjaxForms */ {
        // Public variables and functions.
        /**
         * Attach event listeners to initialise this module.
         *
         * @method init
         * @param {object} args The CSS selector used to find nodes that will trigger this module.
         * @return {Promise}
         */
        init: function(args) {
            return new AjaxForms(args);
        },
        sessdeleteconfirm: function(args){

            return Str.get_strings([{
                key: 'confirm',
                component: 'local_timetable',
            },
            {
                key: 'deleteconfirm',
                component: 'local_timetable',
                param : args.sessionname
            },
            {
                key: 'yes',
                component: 'local_timetable',
            },
            {
                key: 'no',
                component: 'local_timetable',
            },
            {
                key: 'delete'
            }]).then(function(s) {
                ModalFactory.create({
                    title: s[0],
                    type: ModalFactory.types.DEFAULT,
                    body: s[1],
                    footer: '<button type="button" class="btn btn-primary" data-action="save">'+s[2]+'</button>&nbsp;'
                            + '<button type="button" class="btn btn-secondary" data-action="cancel">'+s[3]+'</button>'
                }).done(function(modal) {
                    this.modal = modal;

                    modal.getRoot().find('[data-action="save"]').on('click', function() {
                        args.confirm = true;
                        var data = {};
                        data.id = args.id;
                        var promise = Ajax.call([{
                            methodname: 'local_timetable_delete_instance',
                            args: data,
                        }]);
                        promise[0].done(function() {
                            window.location.reload();
                            window.location.href = window.location.href;
                       }).fail(function(ex) {
                            // do something with the exception
                        });
                    }.bind(this));
                    modal.getFooter().find('[data-action="cancel"]').on('click', function() {
                        modal.setBody('');
                        modal.destroy();
                    });
                    modal.show();
                }.bind(this));
            }.bind(this));
        },
        semslotdeleteconfirm: function(args){

            return Str.get_strings([{
                key: 'confirm',
                component: 'local_timetable',
            },
            {
                key: 'deleteconfirmslots',
                component: 'local_timetable',
                param : args.levelname
            },
            {
                key: 'yes',
                component: 'local_timetable',
            },
            {
                key: 'no',
                component: 'local_timetable',
            },
            {
                key: 'delete'
            }]).then(function(s) {
                ModalFactory.create({
                    title: s[0],
                    type: ModalFactory.types.DEFAULT,
                    body: s[1],
                    footer: '<button type="button" class="btn btn-primary" data-action="save">'+s[2]+'</button>&nbsp;'
                            + '<button type="button" class="btn btn-secondary" data-action="cancel">'+s[3]+'</button>'
                }).done(function(modal) {
                    this.modal = modal;

                    modal.getRoot().find('[data-action="save"]').on('click', function() {
                        args.confirm = true;
                        var data = {};
                        data.id = args.id;
                        var promise = Ajax.call([{
                            methodname: 'local_timetable_semester_slots_delete_instance',
                            args: data,
                        }]);
                        promise[0].done(function() {
                            window.location.reload();
                            window.location.href = window.location.href;
                       }).fail(function(ex) {
                            // do something with the exception
                        });
                    }.bind(this));
                    modal.getFooter().find('[data-action="cancel"]').on('click', function() {
                        modal.setBody('');
                        modal.destroy();
                    });
                    modal.show();
                }.bind(this));
            }.bind(this));
        },
        deletesessionConfirm: function(args) {
            return Str.get_strings([{
                key: 'confirm'
            },
            {
                key: 'delete_session',
                component: 'local_timetable',
                param :args.name
            },
            {
                key: 'yes',
                component: 'local_timetable',
            },
            {
                key: 'no',
                component: 'local_timetable',
            },
            ]).then(function(s) {
                ModalFactory.create({
                    title: s[0],
                    type: ModalFactory.types.DEFAULT,
                    body: s[1],
                    footer: '<button type="button" class="btn btn-primary" data-action="save">'+s[2]+'</button>&nbsp;'
                            + '<button type="button" class="btn btn-secondary" data-action="cancel">'+s[3]+'</button>'
                }).done(function(modal) {
                    this.modal = modal;
                    // modal.setSaveButtonText(s[3]);
                    modal.getRoot().find('[data-action="save"]').on('click', function() {
                        args.confirm = true;
                        var params = {};
                        params.id = args.id;
                        params.contextid = args.contextid;
                        var promise = Ajax.call([{
                            methodname: 'local_timetable_'+args.action,
                            args: params
                        }]);
                        promise[0].done(function() {
                            window.location.href = window.location.href+String();
                        }).fail(function() {
                            // do something with the exception
                        });
                    }.bind(this));
                    modal.getFooter().find('[data-action="cancel"]').on('click', function() {
                        modal.setBody('');
                        modal.destroy();
                    });
                    modal.show();
                }.bind(this));
            }.bind(this));
        },
        deletesessiontype: function(args) {
            return Str.get_strings([{
                key: 'confirm'
            },
            {
                key: 'delete_session_type',
                component: 'local_timetable',
                param :args.name
            },
            {
                key: 'yes',
                component: 'local_timetable',
            },
            {
                key: 'no',
                component: 'local_timetable',
            },
            ]).then(function(s) {
                ModalFactory.create({
                    title: s[0],
                    type: ModalFactory.types.DEFAULT,
                    body: s[1],
                    footer: '<button type="button" class="btn btn-primary" data-action="save">'+s[2]+'</button>&nbsp;'
                            + '<button type="button" class="btn btn-secondary" data-action="cancel">'+s[3]+'</button>'
                }).done(function(modal) {
                    this.modal = modal;
                    // modal.setSaveButtonText(s[3]);
                    modal.getRoot().find('[data-action="save"]').on('click', function() {
                        args.confirm = true;
                        var params = {};
                        params.id = args.id;
                        params.contextid = args.contextid;
                        var promise = Ajax.call([{
                            methodname: 'local_timetable_'+args.action,
                            args: params
                        }]);
                        promise[0].done(function() {
                            window.location.href = window.location.href+String();
                        }).fail(function() {
                            // do something with the exception
                        });
                    }.bind(this));
                    modal.getFooter().find('[data-action="cancel"]').on('click', function() {
                        modal.setBody('');
                        modal.destroy();
                    });
                    modal.show();
                }.bind(this));
            }.bind(this));
        },
        sessiontype_notdelete: function(args) {
            if (args.type == 'edit') {
                var skey = 'session_type_not_edit';
            } else {
                var skey = 'session_type_not_delete';
            }
            return Str.get_strings([{
                key: 'confirm'
            },
            {
                key: skey,
                component: 'local_timetable',
                param : args.name
            }]).then(function(s) {
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
        load: function() {}
    };
});
