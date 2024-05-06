define(['jquery', 'core/str', 'core/modal_factory', 'core/modal_events', 'core/fragment', 'core/ajax', 'core/yui', 'core/templates', 'core/notification'],
        function($, Str, ModalFactory, ModalEvents, Fragment, Ajax, Y, Templates, Notification) {
          var formAjax = function(args) {
                this.contextid = args.contextid || 1;
                this.args = args;
                var self=this;
                self.init(args);
          };


        /**
         * @var {Modal} modal
         * @private
         */
        formAjax.prototype.modal = null;

        /**
         * @var {int} contextid
         * @private
         */
        formAjax.prototype.contextid = -1;

           /**
     * Initialise the class.
     *
     * @param {String} selector used to find triggers for the new group modal.
     * @private
     * @return {Promise}
     */
    formAjax.prototype.init = function(args) {
 

      var self = this;

       var self = this;
       if (args.id) {
            var head =  Str.get_string('editclass', 'local_manager',args);
        } else {
           var head = Str.get_string('addclass', 'local_manager');
        }
        return head.then(function(title) {
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

            // Forms are big, we want a big modal.
            this.modal.setLarge();

            // We want to reset the form every time it is opened.
            this.modal.getRoot().on(ModalEvents.hidden, function() {
                 setTimeout(function(){
                    modal.destroy();
                }, 1000);
                this.modal.setBody('');
            }.bind(this));

            // We want to hide the submit buttons every time it is opened.

            this.modal.getFooter().find('[data-action="save"]').on('click', this.submitForm.bind(this));
            this.modal.getFooter().find(['data-action = cancel']).on('click', function(){

              modal.setBody('');
              modal.hide();

              setTimeout(function () {
                  modal.destroy();
              },1000)

             });

            this.modal.getFooter().find('[data-action="cancel"]').on('click', function() {
                modal.hide();
                setTimeout(function(){
                    modal.destroy();
                }, 1000);
                 //modal.destroy();
            });
            this.modal.getRoot().on('submit', 'form', function(form) {
                self.submitformAjax(form, self.args);
            });
            this.modal.show();
            return this.modal;
        }.bind(this));

    };

    formAjax.prototype.getBody = function(formdata) {
        if (typeof formdata === "undefined") {
            formdata = {};
        }
        // Get the content of the modal.
        this.args.jsonformdata = JSON.stringify(formdata);
        return Fragment.loadFragment(this.args.component, this.args.callback, this.contextid, this.args);
       // return Fragment.loadFragment('local_manager', 'custom_class_form', this.contextid, this.args);
    };

    formAjax.prototype.getFooter=function () {

        //var classname=this.args.classname;

        if (this.args.id) {

           // $footer='<button type="button" class="btn btn-primary" data-action="save">Update ' + this.args.classname + ' Class</button>'

           $footer='<button type="button" class="btn btn-primary" data-action="save">Update Class</button>'

        }else{

            $footer='<button type="button" class="btn btn-primary" data-action="save">Create Class</button>'
        }

        $footer += '<button type="button" class="btn btn-secondary" data-action="cancel">Cancel</button>'
        return $footer;
    };


   formAjax.prototype.getcontentFooter=function(){

      $footer = '<button type="button" class="btn btn-secondary" data-action="cancel">Cancel</button>'
      return $footer;

   };

    formAjax.prototype.handleFormSubmissionResponse = function() {

    // We could trigger an event instead.
    // Yuk.
    Y.use('moodle-core-formchangechecker', function() {
        M.core_formchangechecker.reset_form_dirty_state();
    });

          this.modal.hide();
          window.location.reload();
          window.location.href = window.location.href;

    };

   //  /**
   //   * @method handleFormSubmissionFailure
   //   * @private
   //   * @return {Promise}
   //   */
    formAjax.prototype.handleFormSubmissionFailure = function(data) {
        // Oh noes! Epic fail :(
        // Ah wait - this is normal. We need to re-display the form with errors!
        this.modal.setBody(this.getBody(data));
    };

     formAjax.prototype.submitformAjax = function(e, args) {
        // We don't want to do a real form submission.
        e.preventDefault();
        var self = this;
        // Convert all the form elements values to a serialised string.
        var formData = this.modal.getRoot().find('form').serialize();

       // var methodname = args.plugintype + '_' + args.pluginname + '_submit_create_class_form';
        var params = {};
        params.contextid = this.contextid;
        params.id = args.id
        params.jsonformdata = JSON.stringify(formData);


     
        var promise = Ajax.call([{
            methodname: 'local_manager_submit_register',
            args: params
        }]);
        promise[0].done(function(resp){
                self.args.id = resp.id;
                self.args.contextid = resp.contextid;
                self.handleFormSubmissionResponse(self.args);
                //self.successmessage();
                window.location.reload();
                window.location.href = window.location.href;


        }).fail(function(){
            self.handleFormSubmissionFailure(formData);
        });

    };

    formAjax.prototype.submitForm = function(e) {
        e.preventDefault();
        this.modal.getRoot().find('form').submit();
    };

     return  {
        init: function(args) {
           return new formAjax(args);
        },
        // Delete function
        deleteConfirm: function(args){

            return Str.get_strings([{
                key: 'confirm',
                component: 'local_manager',
            },
            {
                key: 'deleteconfirm1',
                component: 'local_manager',
                param : args
            },
            {
                key: 'delete'
            }]).then(function(s) {
                ModalFactory.create({

                    title: s[0],
                    type: ModalFactory.types.DEFAULT,
                    body: s[1],
                    footer: '<button type="button" class="btn btn-primary" data-action="save">Yes! Delete</button>&nbsp;' +
            '<button type="button" class="btn btn-secondary" data-action="cancel">No</button>'
                }).done(function(modal) {
                    this.modal = modal;

                    modal.getRoot().find('[data-action="save"]').on('click', function() {
                        args.confirm = true;
                        var promise = Ajax.call([{
                            methodname: 'local_manager_' + args.action,
                            args: {
                                id: args.id,
                            },
                        }]);
                        promise[0].done(function() {
                            window.location.reload();
                            window.location.href = window.location.href;
                       }).fail(function(ex) {
                            // do something with the exception
                             console.log(ex);
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
        // Hide function
        hideConfirm: function(args){
            return Str.get_strings([{
                key: 'hideconfirmheader',
                component: 'local_manager',
            },
            {
                key: 'hideconfirm',
                component: 'local_manager',
                param : args
            },
            {
                key: 'hide'
            }]).then(function(s) {
                ModalFactory.create({
                    title: s[0],
                    type: ModalFactory.types.DEFAULT,
                    body: s[1],
                    footer: '<button type="button" class="btn btn-primary" data-action="save">Yes! Hide</button>&nbsp;' +
            '<button type="button" class="btn btn-secondary" data-action="cancel">No</button>'
                }).done(function(modal) {
                    this.modal = modal;

                    modal.getRoot().find('[data-action="save"]').on('click', function() {
                        args.confirm = true;
                        var promise = Ajax.call([{
                            methodname: 'local_manager_' + args.action,
                            args: {
                                // contextid: args.contextid,
                                id: args.id,
                                // classname: args.classname,
                            },
                        }]);
                        promise[0].done(function() {
                            window.location.reload();
                            window.location.href = window.location.href;
                        }).fail(function(ex) {
                            // do something with the exception
                             console.log(ex);
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
        // Unhide function
        unHideConfirm: function(args){
            return Str.get_strings([{
                key: 'unhideconfirmheader',
                component: 'local_manager',
            },
            {
                key: 'unhideconfirm',
                component: 'local_manager',
                param : args
            },
            {
                key: 'hide'
            }]).then(function(s) {
                ModalFactory.create({
                    title: s[0],
                    type: ModalFactory.types.DEFAULT,
                    body: s[1],
                    footer: '<button type="button" class="btn btn-primary" data-action="save">Yes! UnHide</button>&nbsp;' +
            '<button type="button" class="btn btn-secondary" data-action="cancel">No</button>'
                }).done(function(modal) {
                    this.modal = modal;

                    modal.getRoot().find('[data-action="save"]').on('click', function() {
                        args.confirm = true;
                        var promise = Ajax.call([{
                            methodname: 'local_manager_' + args.action,
                            args: {
                                // contextid: args.contextid,
                                id: args.id,
                                // classname: args.classname,
                            },
                        }]);
                        promise[0].done(function() {
                            window.location.reload();
                            window.location.href = window.location.href;
                        }).fail(function(ex) {
                            // do something with the exception
                             console.log(ex);
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

       load:function () {


       }

    };

});
