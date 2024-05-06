/**
 * Streaming video js
 *
 * @module     block_stream/stream
 * @class      stream
 * @package    block_stream
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
define(['jquery', 'core/str', 'core/modal_factory', 'core/modal_events', 'core/fragment', 'core/ajax', 'core/yui'],
        function($, Str, ModalFactory, ModalEvents, Fragment, Ajax, Y) {
    var upload = function(args) {
        this.contextid = 1;
        var self = this;
        this.args = args;
        self.init(args);
    };
    upload.prototype.init = function(args) {
        var self = this;
        return Str.get_string('uploadvideo', 'block_stream').then(function(title) {
                // Create the modal.
                return ModalFactory.create({
                type: ModalFactory.types.DEFAULT,
                title: title,
                body: this.getBody(),
                footer: this.getFooter(),
                });
            }.bind(this)).then(function(modal) {
                // Keep a reference to the modal.
                this.modal = modal;
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
                    // modal.destroy();
                });

                this.modal.getRoot().on('submit', 'form', function(form) {
                    self.submitFormAjax(form, self.args);
                });
                this.modal.show();

                return this.modal;
            }.bind(this));
    };
    upload.prototype.getBody = function(formdata) {
        if (typeof formdata === "undefined") {
            formdata = {};
        }
        this.args.jsonformdata = JSON.stringify(formdata);
        return Fragment.loadFragment('block_stream', 'upload_video', this.contextid, this.args);
    };
    upload.prototype.getFooter = function() {
        $footer = '<button type="button" class="btn btn-primary" data-action="save">Save & Continue</button>&nbsp;';
        $footer += '<button type="button" class="btn btn-secondary" data-action="cancel">Cancel</button>';
        return $footer;
    };
    upload.prototype.handleFormSubmissionFailure = function(data) {
        // Oh noes! Epic fail :(
        // Ah wait - this is normal. We need to re-display the form with errors!
        this.modal.setBody(this.getBody(data));
    };
    upload.prototype.submitFormAjax = function(e ,args) {
        // We don't want to do a real form submission.
        e.preventDefault();
        var self = this;
        // Convert all the form elements values to a serialised string.
        var formData = this.modal.getRoot().find('form').serialize();
        // var methodname = args.plugintype + '_' + args.pluginname + '_submit_create_user_form';
        var methodname = 'block_stream_upload_video';
        var params = {};
        params.contextid = this.contextid;
        params.jsonformdata = JSON.stringify(formData);
        var promise = Ajax.call([{
            methodname: methodname,
            args: params
        }]);

         promise[0].done(function(resp){
            self.modal.hide();
            window.location.reload();
        }).fail(function(ex){
            self.handleFormSubmissionFailure(formData);
        });
    };
    upload.prototype.submitForm = function(e) {
        e.preventDefault();
        var self = this;
        self.modal.getRoot().find('form').submit();
    };
    return {
        init: function(args) {
            return new upload(args);
        }
    }
});