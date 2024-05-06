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
        'core/templates',
        'local_program/select2',
        'local_program/program'],
        function($, Str, ModalFactory, ModalEvents, Fragment, Ajax, Y, Templates, select2, program) {

    /**
     * Constructor
     * @param {object} args
     * Each call to init gets it's own instance of this class.
     */
   var programlastchildpopup = function(args) {
            $.ajax({
                type: "POST",
                url:   M.cfg.wwwroot + '/local/program/ajax.php',
                data: { programid: args.id, action:'programlastchildpopup',
                    sesskey: M.cfg.sesskey
                },
                success: function(returndata) {
                    //Var returned_data is ONLY available inside this fn!
                    ModalFactory.create({
                        title: Str.get_string('program_info', 'local_program'),
                        body: returndata
                      }).done(function(modal) {
                        // Do what you want with your new modal.
                        modal.show();
                         modal.setLarge();
                         modal.getRoot().addClass('openLMStransition');
                            modal.getRoot().animate({"right":"0%"}, 500);
                            modal.getRoot().on(ModalEvents.hidden, function() {
                            modal.getRoot().animate({"right":"-85%"}, 500);
                                    setTimeout(function(){
                                    modal.destroy();
                                }, 1000);
                            }.bind(this));
                            $(".close").click(function(){
                                window.location.href =  window.location.href + String();
                            });
                      });
                }
            });
    };
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
                case 'program_form':
                    switch (args.id) {
                        case 0:
                            header_label = {key:'createprogram', component:'local_program'};
                        break;
                        default:
                            header_label = {key:'updateprogram', component:'local_program'};
                        break;
                    }
                break;
                case 'session_form':
                    switch (args.id) {
                        case 0:
                            header_label = {key:'addsession', component:'local_program'};
                        break;
                        default:
                            header_label = {key:'updatesession', component:'local_program'};
                        break;
                    }
                break;
                case 'course_form':
                    switch (args.id) {
                        case 0:
                            header_label = {key:'addcourses', component:'local_program', param:args.programname};
                        break;
                        default:
                            header_label = {key:'updatecourses', component:'local_program'};
                        break;
                    }
                break;
                case 'program_completion_form':
                    header_label = {key:'program_completion_settings', component:'local_program'};
                break;
                case 'program_curriculum_form':
                    header_label = {key:'program_curriculum', component:'local_program'};
                break;
                case 'program_managelevel_form':
                    switch (args.id) {
                         case 0:
                              header_label = {key:'addsemester', component:'local_program'};
                         break;
                         default:
                              header_label = {key:'updatelevel', component:'local_program'};
                         break;
                    }

            }
        var customstrings = Str.get_strings([header_label,{
                        key: 'savecontinue',
                        component: 'local_program'
                    },
                    {
                        key: 'assign',
                        component: 'local_program'
                    },
                    {
                        key: 'save',
                        component: 'local_program'
                    },
                    {
                        key: 'previous',
                        component: 'local_program'
                    },
                    {
                        key: 'skip',
                        component: 'local_program'
                    },
                    {
                        key: 'cancel',
                        component: 'local_program'
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

            this.modal.getRoot().addClass('openLMStransition local_program');

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
                window.location.reload();
                // window.location.href =  window.location.href + String();
                if (args.prevlvlid > 0 || args.prevlvlid == 0) {
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
                } else if(args.callback === 'program_curriculum_form' || args.callback === 'program_form') {
                    modal.destroy();
                } else if(args.pluginname === 'program_addlevel' || args.callback === 'program_managelevel_form') {
                    if (args.id == 0) {
                        modal.destroy();
                    } else {
                        $.ajax({
                            method: 'POST',
                            url: M.cfg.wwwroot + '/local/program/ajax.php',
                            data: {
                                action: 'programlevelcourses',
                                programid:args.programid,
                                levelid: args.id
                            },
                            success:function(resp){
                                $('.levetabscontent_container').html(resp);
                            }
                        });
                    }
                } else {
                    $.ajax({
                        method: 'POST',
                        url: M.cfg.wwwroot + '/local/program/ajax.php',
                        data: {
                            action: 'programlevelcourses',
                            programid:args.programid,
                            levelid: args.id
                        },
                        success:function(resp){
                            $('.levetabscontent_container').html(resp);
                        }
                    });
                }
            });
            this.modal.getFooter().find('[data-action="skip"]').on('click', function() {
                self.args.form_status = self.args.form_status + 1;
                 if (args.callback == 'program_form') {
                 }
                var data = self.getBody();
                data.then(function(html) {
                    if (html === false) {
                        self.handleFormSubmissionResponse(args);
                        if (!$.fn.DataTable.isDataTable('#viewprograms')){
                          $('#viewprograms').dataTable({
                              'language': {
                                  'paginate': {
                                  'previous': '<',
                                  'next': '>'
                              },
                          },
                          'bInfo': false,
                          }).destroy();
                        }
                        program.Datatable();
                    }
                });
                modal.setBody(data);
            });

            this.modal.getRoot().on('submit', 'form', function(form) {
                self.submitFormAjax(form, self.args);
            });
            this.modal.show();
            this.modal.getRoot().animate({"right":"0%"}, 500);
            $(".close").click(function(){
                window.location.href =  window.location.href + String();
            });
            this.modal.show();
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
    AjaxForms.prototype.getFooter = function(customstrings) {
        var $style, $footer;
        $footer = '<button id="id_submit" type="button" class="btn btn-primary before_submit" data-action="save">'+customstrings[1]+'</button>&nbsp;';
        $footer += '<button type="button" class="btn btn-secondary" data-action="cancel">'+customstrings[6]+'</button>&nbsp;';
        if (this.args.form_status == 0) {
            $style = 'style="display:none;"';

            $footer += '<button id="id_submit" type="button" class="btn btn-secondary" data-action="skip" '
            + $style + '>'+customstrings[5]+'</button>&nbsp;';
        } else {
            $footer = '<button type="button" class="btn btn-secondary" data-action="cancel">'+customstrings[6]+'</button>';
        }
        return $footer;
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
        if (args.form_status == -2) {
            this.modal.destroy();
            if (args.levelid > 0 && (args.prevlvlid > 0 || args.prevlvlid == 0)) {
                $('.programlevels #semlvlid'+args.prevlvlid).find('a').trigger('click');
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
            } else if(args.id > 0 && args.programid > 0) {
                var pop = this.modal;
                if (args.pluginname === 'program_addlevel') {
                    if (args.newsemadd == 0) {
                        window.location.href =  window.location.href + String();
                    } else {
                        pop.destroy();
                        // window.location.href =  window.location.href + String();
                        $.ajax({
                            method: 'POST',
                            url: M.cfg.wwwroot + '/local/program/ajax.php',
                            data: {
                                action: 'programlevelcourses',
                                programid: args.programid,
                                levelid: args.id
                            },
                            success:function(resp){
                                $('.levetabscontent_container').html(resp);
                            }
                        });
                    }
                }
            } else if(args.prevlvlid == 0 || args.prevlvlid == null) {
                this.modal.destroy();
                $(document).on('click', '#page-local-program-view .modal-content .modal-footer .btn-primary', function() {
                        // this.modal.destroy();
                        // window.location.href =  window.location.href + String();
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
                });
                // window.location.href =  window.location.href + String();
                $('.programlevels #semlvlid'+args.id).find('a').trigger('click');
                $('#levtabcont'+args.id+' .tab-content').load(' #levtabcont'+args.id+' .tabcontent');
            } else if (args.prevlvlid > 0 || args.prevlvlid == 0) {
                $(document).on('click', '#page-local-program-view .modal-content .modal-footer .btn-primary', function() {
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
                });
            } else {
                $('.programlevels #semlvlid'+args.prevlvlid).trigger('click');
                $('.programlevels #semlvlid'+args.id).trigger('click');
            }
        }
        if (args.callback === 'program_form') {
            programlastchildpopup(args);
        }

        if (args.pluginname === 'program_addlevel') {
            window.location.reload();
        }
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
        e.preventDefault();
        var self = this;
        // Convert all the form elements values to a serialised string.
        var formData = this.modal.getRoot().find('form').serialize();
        var methodname = args.plugintype + '_' + args.pluginname + '_submit_instance';
        // Now we can continue...
        var params = {};
        params.contextid = this.contextid;
        params.jsonformdata = JSON.stringify(formData);
        params.form_status = args.form_status;

        var promise = Ajax.call([{
            methodname: methodname,
            args: params
        }]);

           $(document).on('click', '#id_submit', function(){
                   $(this).removeClass('before_submit').addClass('not-allowed');
               });
               // let footer = Y.one('.modal-footer');
               // let spinner = M.util.add_spinner(Y, footer);
               // spinner.show();

        promise[0].done(function(resp){
            self.args.form_status = resp.form_status;
            if (resp.form_status >= 0 && resp.form_status !== false) {
                self.args.form_status = resp.form_status;
                self.args.id = resp.id;
                self.handleFormSubmissionFailure();
            } else {
                self.args.id = resp.id;
                self.handleFormSubmissionResponse(self.args);
            }
            if (args.form_status > 0) {
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
        load:function(){

        }
    };
});
