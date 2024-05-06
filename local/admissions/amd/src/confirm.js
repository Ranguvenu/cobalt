/**
 * Add a create new group modal to the page.
 *
 * @module     local_admissions/confirm
 * @class      confirm
 * @package
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
define(['jquery', 'core/str', 'core/modal_factory', 'core/modal_events',
'core/fragment', 'core/ajax', 'core/yui'],
    function($, Str, ModalFactory, ModalEvents, Fragment, Ajax, Y) {

/**
 * Constructor
 * @param {String} args used to find triggers for the new group modal.
 * Each call to init gets it's own instance of this class.
 */
var confirm = function(args) {

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
confirm.prototype.modal = null;
/**
 * @var {int} contextid
 * @private
 */
confirm.prototype.contextid = -1;
/**
 * Initialise the class.
 * @private
 * @return {Promise}
 */
confirm.prototype.init = function() {
    var self = this;
    // Fetch the title string.
        if (self.id) {
            var strings  = Str.get_strings([
                            {
                                key: 'cancel',
                                component: 'local_admissions'
                            },
                            {
                                key: 'save_continue',
                                component: 'local_admissions'
                            },
                            {
                                key: 'reviseapplication',
                                component: 'local_admissions'
                            },
                            {
                                key: 'previous',
                                component: 'local_admissions'
                            },
                            {
                                key: 'cancel',
                                component: 'local_admissions'
                            }]);
        }else{
           var strings  = Str.get_strings([
                        {
                            key: 'cancel',
                            component: 'local_admissions'
                        },
                        {
                            key: 'save_continue',
                            component: 'local_admissions'
                        },
                        {
                            key: 'reviseapplication',
                            component: 'local_admissions'
                        },
                        {
                            key: 'previous',
                            component: 'local_admissions'
                        },
                        {
                            key: 'cancel',
                            component: 'local_admissions'
                        }]);
        }

        return strings.then(function(strings) {
            // Create the modal.
            return ModalFactory.create({
                type: ModalFactory.types.DEFAULT,
                title: strings[2],
                body: this.getBody(),
                footer: this.getFooter(strings),
            });
        }.bind(this)).then(function(modal) {
            // Keep a reference to the modal.
            this.modal = modal;
            // Forms are big, we want a big modal.
            this.modal.setLarge(false);

            this.modal.getFooter().find('[data-action="save"]').on('click', this.submitForm.bind(this));
            // We also catch the form submit event and use it to submit the form with ajax.
            this.modal.getFooter().find('[data-action="cancel"]').on('click', function() {
                modal.hide();
                setTimeout(function(){
                    modal.destroy();
                }, 500);
                window.location.reload();
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
confirm.prototype.getBody = function(formdata) {
    if (typeof formdata === "undefined") {
        formdata = {};
    }

    this.args.jsonformdata = JSON.stringify(formdata);
    return Fragment.loadFragment('local_admissions', 'new_statusconfirmform', this.contextid, this.args);
};
    /**
    * @param {string} strings The CSS selector used to find nodes that will trigger this module.
    * @method getFooter
    * @private
    * @return {Promise}
    */
     confirm.prototype.getFooter = function(strings) {
       var $footer;
       var $style;
        $footer = '<button type="button" class="btn btn-secondary" data-action="cancel">'+ strings[0] +'</button>';
       $footer += '<button id="id_revise" type="button" class="btn btn-primary before_revise" data-action="save">'+
                    strings[1] +'</button>&nbsp;';
       $style = 'style="display:none;"';
    //    $footer += '<button type="button" class="btn btn-secondary" data-action="previous" '
    //             + $style + ' >'+ strings[3] +'</button>&nbsp;';
    //    $footer += '<button type="button" class="btn btn-secondary" data-action="skip" ' +
    //             $style + ' >'+ strings[2] +'</button>&nbsp;';

       return $footer;
   };
       /**
    * @method handleFormSubmissionResponse
    * @private
    * @return {Promise}
    */
        confirm.prototype.handleFormSubmissionResponse = function() {
           this.modal.destroy();
           // We could trigger an event instead.
           // Yuk.
           Y.use('moodle-core-formchangechecker', function() {
               M.core_formchangechecker.reset_form_dirty_state();
           });
           // This will be the context for our template. So {{name}} in the template will resolve to "Tweety bird".
           window.location.reload();
       };
       /**
        * @param {object} data
        * @method handleFormSubmissionFailure
        * @private
        * @return {Promise}
        */
       confirm.prototype.handleFormSubmissionFailure = function(data) {
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
       confirm.prototype.submitFormAjax = function(e ,args) {
           // We don't want to do a real form submission.
           e.preventDefault();
           var self = this;
           // Convert all the form elements values to a serialised string.
           var formData = this.modal.getRoot().find('form').serialize();
           var methodname = 'local_admissions_status_statusconfirmform';
           var params = {};
           params.contextid = args.context;
           params.admissionid = args.admissionid;
           params.programid = args.programid;
           params.jsonformdata = JSON.stringify(formData);

           if(document.getElementById("id_reason").value.length !== 0)
           {
               $(document).on('click', '#id_revise', function(){
                   $(this).removeClass('before_revise').addClass('not-allowed');
               });
               let footer = Y.one('.modal-footer');
               let spinner = M.util.add_spinner(Y, footer);
               spinner.show();
           }

           var promise = Ajax.call([{
               methodname: methodname,
               args: params
           }]);
           promise[0].done(function(resp){
            self.handleFormSubmissionResponse();
               if(resp) {
                   self.args.admissionid = resp.admissionid;
                   self.args.programid = resp.programid;
                   self.handleFormSubmissionFailure();
               } else {
                   self.modal.destroy();
                   window.location.reload();
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
   confirm.prototype.submitForm = function(e) {
       e.preventDefault();
       var self = this;
       self.modal.getRoot().find('form').submit();
   };
   return /** @alias module:local_admissins/confirm */ {
       // Public variables and functions.
       /**
        * Attach event listeners to initialise this module.
        *
        * @method init
        * @param {string} args The CSS selector used to find nodes that will trigger this module.
        * @return {Promise}
        */
       init: function(args) {
           return new confirm(args);
       },
       load: function(){
       },
   };
});
