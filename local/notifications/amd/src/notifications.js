/**
 * Add a create new group modal to the page.
 *
 * @module     local_notification/newnotification
 * @class      NewNotification
 * @package
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
define(['jquery', 'core/str', 'core/modal_factory', 'core/modal_events', 'core/fragment', 'core/ajax'],
        function($, Str, ModalFactory, ModalEvents, Fragment, Ajax) {
    /**
     * Constructor
     *
     * @param {object} args used to find triggers for the new group modal.
     * @param {object} notificationid
     * @param {object} instance
     * @param {object} plugin
     *
     * Each call to init gets it's own instance of this class.
     */
    var NewNotification = function(args, notificationid, instance, plugin) {

        this.contextid = args.context;
        this.id = args.id;
        this.notificationid = notificationid;
        this.instance = instance;
        this.plugin = plugin;
        var self = this;
        this.args = args;
        self.init(args);
    };
    /**
     * @var {Modal} modal
     * @private
     */
    NewNotification.prototype.modal = null;
    /**
     * @var {int} contextid
     * @private
     */
    NewNotification.prototype.contextid = -1;
    /**
     * Initialise the class.
     *
     * @param {object} args used to find triggers for the new group modal.
     * @private
     * @return {Promise}
     */
    NewNotification.prototype.init = function(args) {
        //var triggers = $(selector);
        var self = this;
        // Fetch the title string.
            if (args.id) {
                self.notificationid = args.id;
            } else {
                self.notificationid = 0;
            }
            if (self.notificationid) {
                var head =  {key: 'update_notification', component: 'local_notifications'};
            } else {
               var head =  {key: 'create_notification', component: 'local_notifications'};
            }
            var strings = Str.get_strings([head
            , {
                key: 'save_continue',
                component: 'local_users'
            }, {
                key: 'cancel',
                component: 'moodle'
            }, {
                key: 'no',
                component: 'moodle'
            }]);
            return strings.then(function(str) {
                // Create the modal.
                return ModalFactory.create({
                type: ModalFactory.types.DEFAULT,
                title: str[0],
                body: this.getBody(),
                footer: this.getFooter(str),
                });
            }.bind(this)).then(function(modal) {
                // Keep a reference to the modal.
                this.modal = modal;
                // Forms are big, we want a big modal.
                this.modal.setLarge();
                this.modal.getRoot().addClass('openLMStransition local_notifications');

                this.modal.getRoot().on(ModalEvents.hidden, function() {
                    this.modal.getRoot().animate({"right":"-85%"}, 500);
                        modal.destroy();
                }.bind(this));

                this.modal.getFooter().find('[data-action="save"]').on('click', this.submitForm.bind(this));
                // We also catch the form submit event and use it to submit the form with ajax.

                this.modal.getFooter().find('[data-action="cancel"]').on('click', function() {
                    modal.hide();
                    setTimeout(function(){
                        modal.destroy();
                    }, 5000);
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
     * @method getBody
     * @private
     * @param {object} formdata
     * @return {Promise}
     */
    NewNotification.prototype.getBody = function(formdata) {
        if (typeof formdata === "undefined") {
            formdata = {};
        }
        this.args.jsonformdata = JSON.stringify(formdata);
        return Fragment.loadFragment('local_notifications', 'new_notification_form', this.contextid, this.args);
    };
    /**
     * @method getFooter
     * @private
     * @param {object} str
     * @return {Promise}
     */
    NewNotification.prototype.getFooter = function(str) {
        var $footer;
        $footer = '<button type="button" class="btn btn-primary" data-action="save">'+str[1]+'</button>&nbsp;';
        $footer += '<button type="button" class="btn btn-secondary" data-action="cancel">'+str[2]+'</button>';
        return $footer;
    };
    /**
     * @method handleFormSubmissionFailure
     * @private
     * @param {object} data
     * @return {Promise}
     */
    NewNotification.prototype.handleFormSubmissionFailure = function(data) {
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
    NewNotification.prototype.submitFormAjax = function(e ,args) {
        // We don't want to do a real form submission.
        e.preventDefault();
        var self = this;
        // Convert all the form elements values to a serialised string.
        var formData = this.modal.getRoot().find('form').serialize();
        var methodname = 'local_notifications_submit_create_notification_form';
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
    NewNotification.prototype.submitForm = function(e) {
        e.preventDefault();
        var self = this;
        self.modal.getRoot().find('form').submit();
    };
    return /** @alias module:local_users/newuser */ {
        // Public variables and functions.
        /**
         * Attach event listeners to initialise this module.
         *
         * @method init
         * @param {object} args
         * @return {Promise}
         */
        init: function(args) {
            return new NewNotification(args);
        },
        load: function(){

        }
    };
});
