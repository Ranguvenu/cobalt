/**
 * Add a create new group modal to the page.
 *
 * @module     local_location/location
 * @class      NewInstitute
 * @package    local_location
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
define(['jquery', 'core/str', 'core/modal_factory', 'core/modal_events', 'core/fragment', 'core/ajax', 'core/yui'],
        function($, Str, ModalFactory, ModalEvents, Fragment, Ajax, Y) {

    /**
     * Constructor
     *
     * @param {String} selector used to find triggers for the new group modal.
     * @param {int} contextid
     *
     * Each call to init gets it's own instance of this class.
     */
    var NewInstitute = function(args) {
        this.contextid = args.contextid;


        this.instituteid = args.instituteid;
        var self = this;
        self.init(args.selector);
    };

    /**
     * @var {Modal} modal
     * @private
     */
    NewInstitute.prototype.modal = null;

    /**
     * @var {int} contextid
     * @private
     */
    NewInstitute.prototype.contextid = -1;

    /**
     * Initialise the class.
     *
     * @param {String} selector used to find triggers for the new group modal.
     * @private
     * @return {Promise}
     */
    NewInstitute.prototype.init = function(args) {
        var self = this;
        // Fetch the title string.
        // $('.'+args.selector).click(function(){
            var editid = $(this).data('value');
            if (editid) {
                self.instituteid = editid;
            }
            if(this.instituteid){
                var head = Str.get_string('updateinstitute', 'local_location');
            }else{
                var head = Str.get_string('adnewinstitute', 'local_location');
            }
            return head.then(function(title) {
                // Create the modal.
                return ModalFactory.create({
                    type: ModalFactory.types.SAVE_CANCEL,
                    title: title,
                    body: self.getBody()
                });
            }.bind(self)).then(function(modal) {

                // Keep a reference to the modal.
                self.modal = modal;
                // self.modal.show();
                // Forms are big, we want a big modal.
                self.modal.setLarge();
                this.modal.getRoot().addClass('openLMStransition');

                // We want to reset the form every time it is opened.
                this.modal.getRoot().on(ModalEvents.hidden, function() {
                    this.modal.getRoot().animate({"right":"-85%"}, 500);
                    setTimeout(function(){
                        modal.destroy();
                    }, 5000);
                }.bind(this));

                // We want to hide the submit buttons every time it is opened.
                self.modal.getRoot().on(ModalEvents.shown, function() {
                    $('#page-local-location-index .close').on('click', function(){
                        window.location.reload();
                    });
                    $(document).keyup(function(e) {    
                        if (e.keyCode == 27) { //escape key
                            window.location.reload();
                        }
                    });
                    self.modal.getRoot().append('<style>[data-fieldtype=submit] { display: none ! important; }</style>');
                    this.modal.getFooter().find('[data-action="cancel"]').on('click', function() {
                        modal.destroy();
                        window.location.reload();
                        setTimeout(function(){
                            modal.destroy();
                        }, 5000);
                        // modal.destroy();
                    });
                }.bind(this));


                // We catch the modal save event, and use it to submit the form inside the modal.
                // Triggering a form submission will give JS validation scripts a chance to check for errors.
                self.modal.getRoot().on(ModalEvents.save, self.submitForm.bind(self));
                // We also catch the form submit event and use it to submit the form with ajax.
                self.modal.getRoot().on('submit', 'form', self.submitFormAjax.bind(self));
                self.modal.show();
                this.modal.getRoot().animate({"right":"0%"}, 500);
                return this.modal;
            }.bind(this));
        // });
    };

    /**
     * @method getBody
     * @private
     * @return {Promise}
     */
    NewInstitute.prototype.getBody = function(formdata) {
        if (typeof formdata === "undefined") {
            formdata = {};
        }
        // alert(formdata);
        // Get the content of the modal.
        var params = {instituteid:this.instituteid, jsonformdata: JSON.stringify(formdata)};
        return Fragment.loadFragment('local_location', 'new_instituteform', this.contextid, params);
    };

    /**
     * @method handleFormSubmissionResponse
     * @private
     * @return {Promise}
     */
    NewInstitute.prototype.handleFormSubmissionResponse = function() {
        this.modal.destroy();
        // We could trigger an event instead.
        // Yuk.
        Y.use('moodle-core-formchangechecker', function() {
            M.core_formchangechecker.reset_form_dirty_state();
        });
        document.location.reload();
    };

    /**
     * @method handleFormSubmissionFailure
     * @private
     * @return {Promise}
     */
    NewInstitute.prototype.handleFormSubmissionFailure = function(data) {
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
     */
    NewInstitute.prototype.submitFormAjax = function(e) {
        // We don't want to do a real form submission.
        e.preventDefault();

        // Convert all the form elements values to a serialised string.
        var formData = this.modal.getRoot().find('form').serialize();
        // alert(this.contextid);
        // Now we can continue...
        Ajax.call([{
            methodname: 'local_location_submit_instituteform_form',
            args: {contextid: this.contextid, jsonformdata: JSON.stringify(formData)},
            done: this.handleFormSubmissionResponse.bind(this, formData),
            fail: this.handleFormSubmissionFailure.bind(this, formData)
        }]);
    };

    /**
     * This triggers a form submission, so that any mform elements can do final tricks before the form submission is processed.
     *
     * @method submitForm
     * @param {Event} e Form submission event.
     * @private
     */
    NewInstitute.prototype.submitForm = function(e) {
        e.preventDefault();
        var self = this;
        self.modal.getRoot().find('form').submit();
    };

    return /** @alias module:local_location/newlocation */ {
        // Public variables and functions.
        /**
         * Attach event listeners to initialise this module.
         *
         * @method init
         * @param {string} selector The CSS selector used to find nodes that will trigger this module.
         * @param {int} contextid The contextid for the course.
         * @return {Promise}
         */
        init: function(args) {

            // alert(args.contextid);
            return new NewInstitute(args);
        },
        locationConfirm: function(args) {
            return Str.get_strings([{
                key: 'confirm'
            },
            {
                key: 'delete_location',
                component: 'local_location',
                param :args.fullname
            },
            {
                key: 'yes',
                component: 'local_location',
            },
            {
                key: 'no',
                component: 'local_location',
            }
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
                        var promise = Ajax.call([{
                            methodname: 'local_location_'+args.action,
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
        location_not_Confirm: function(args) {
            if (args.type == 'edit') {
                var skey = 'building_not_edit';
            } else {
                var skey = 'building_not_delete';
            }
            return Str.get_strings([{
                key: 'confirm'
            },
            {
                key: skey,
                component: 'local_location',
                param : args.fullname
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
        load: function(){

        }
    };
});
