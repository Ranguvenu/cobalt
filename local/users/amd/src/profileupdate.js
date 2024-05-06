/**
 * Add a create new group modal to the page.
 *
 * @module     local_users/profileupdate
 * @class      ProfileUpdate
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
    var ProfileUpdate = function(args) {

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
    ProfileUpdate.prototype.modal = null;

    /**
     * @var {int} contextid
     * @private
     */
    ProfileUpdate.prototype.contextid = -1;

    /**
     * Initialise the class.
     * @private
     * @return {Promise}
     */
    ProfileUpdate.prototype.init = function() {
        var self = this;
        // Fetch the title string.
            if (self.id) {
                var strings  = Str.get_strings([
                                {
                                    key: 'profileupdate',
                                    component: 'local_users'
                                },
                                {
                                    key: 'save_continue',
                                    component: 'local_users'
                                },
                                {
                                    key: 'skip',
                                    component: 'local_users'
                                },
                                {
                                    key: 'previous',
                                    component: 'local_users'
                                },
                                {
                                    key: 'cancel',
                                    component: 'local_users'
                                }]);
            }else{
               var strings  = Str.get_strings([
                            {
                                key: 'adnewuser',
                                component: 'local_users'
                            },
                            {
                                key: 'save_continue',
                                component: 'local_users'
                            },
                            {
                                key: 'skip',
                                component: 'local_users'
                            },
                            {
                                key: 'previous',
                                component: 'local_users'
                            },
                            {
                                key: 'cancel',
                                component: 'local_users'
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

                this.modal.getFooter().find('[data-action="skip"]').on('click', function() {
                    self.args.form_status = self.args.form_status + 1;
                    var data = self.getBody();
                    data.then(function(html) {
                        if(html === false) {
                            window.location.reload();
                        }
                    });
                    modal.setBody(data);
                    if(self.args.form_status==2){
                        $('[data-action="skip"]').css('display', 'none');
                    }
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
                        $('[data-action="skip"]').css('display', 'none');
                        $('[data-action="previous"]').css('display', 'none');
                    }else{
                        $('[data-action="skip"]').css('display', 'block');
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
     * @param {object} formdata
     * @method getBody
     * @private
     * @return {Promise}
     */
    ProfileUpdate.prototype.getBody = function(formdata) {
        if (typeof formdata === "undefined") {
            formdata = {};
        }
        this.args.jsonformdata = JSON.stringify(formdata);
        return Fragment.loadFragment('local_users', 'update_user_profile', this.contextid, this.args);
    };
    /**
     * @param {String} strings used to find triggers for the new group modal.
     * @method getFooter
     * @private
     * @return {Promise}
     */
    ProfileUpdate.prototype.getFooter = function(strings) {
        var $footer;
        var $style;
$footer = '<button type="button" class="btn btn-primary" data-action="save">'+ strings[1] +'</button>&nbsp;';
$style = 'style="display:none;"';
$footer += '<button type="button" class="btn btn-secondary" data-action="previous" ' + $style + ' >'+ strings[3] +'</button>&nbsp;';
$footer += '<button type="button" class="btn btn-secondary" data-action="skip" ' + $style + ' >'+ strings[2] +'</button>&nbsp;';
$footer += '<button type="button" class="btn btn-secondary" data-action="cancel">'+ strings[4] +'</button>';
        return $footer;
    };

    /**
     * @method handleFormSubmissionResponse
     * @private
     * @return {Promise}
     */
    ProfileUpdate.prototype.handleFormSubmissionResponse = function() {
        this.modal.hide();
        // We could trigger an event instead.
        // Yuk.
        Y.use('moodle-core-formchangechecker', function() {
            M.core_formchangechecker.reset_form_dirty_state();
        });
        // This will be the context for our template. So {{name}} in the template will resolve to "Tweety bird".
        var args;
        var Templates;
        var context = { id: args.id};
         // This will call the function to load and render our template.
        // It returns a promise that needs to be resoved.

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
    ProfileUpdate.prototype.handleFormSubmissionFailure = function(data) {
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
    ProfileUpdate.prototype.submitFormAjax = function(e ,args) {
        // We don't want to do a real form submission.
        e.preventDefault();
        var self = this;
        // Convert all the form elements values to a serialised string.
        var formData = this.modal.getRoot().find('form').serialize();
        var methodname = 'local_users_submit_profile_update_form';
        var params = {};
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
                $('[data-action="skip"]').css('display', 'inline-block');
                $('[data-action="previous"]').css('display', 'inline-block');
            }

            if(args.form_status == 2) {
                $('[data-action="skip"]').css('display', 'none');
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
    ProfileUpdate.prototype.submitForm = function(e) {
        e.preventDefault();
        var self = this;
        self.modal.getRoot().find('form').submit();
    };
    return /** @alias module:local_users/profileupdate */ {
        // Public variables and functions.
        /**
         * Attach event listeners to initialise this module.
         *
         * @method init
         * @param {string} args The CSS selector used to find nodes that will trigger this module.
         * @return {Promise}
         */
        init: function(args) {
            return new ProfileUpdate(args);
        },
        load: function(){
        },
           thankyou: function(args) {
            return Str.get_strings([{
                key: 'activeconfirm',
                component: 'local_users',
                param: args,
            }]).then(function() {
                ModalFactory.create({
                    type: ModalFactory.types.DEFAULT,
                    body: '<div class="thankyoumsg">Thank you.</div>'+
        '<button type="button" class="btn btn-primary" data-action="save">Start<i class="fa fa-angle-right"></i></button>',
                }).done(function(modal) {
                    this.modal = modal;
                    this.modal.header.hide();
                    this.modal.body.addClass('thankyouscreen');
                    modal.getRoot().find('[data-action="save"]').on('click', function() {
                        window.location.href = M.cfg.wwwroot+'/my/dashboard.php';
                    }.bind(this));
                    modal.show();
                }.bind(this));
            }.bind(this));
        },
    };
});