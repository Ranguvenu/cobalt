/**
 * Add a create new group modal to the page.
 *
 * @module     core_group/AjaxForms
 * @class      AjaxForms
 * @package
 * @copyright  2022 Eabyas Info Solutions <www.eabyas.in>
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
      ],/*globals modal*/
        function($, Str, ModalFactory, ModalEvents, Fragment, Ajax, Y) {
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
     * @param {String} args used to find triggers for the new group modal.
     * @private
     * @return {Promise}
     */
    AjaxForms.prototype.init = function(args) {
        // Fetch the title string.
        var header_label;
        var self = this;
            switch (args.callback) {
                case 'curriculum_form':

                    switch (args.id) {
                        case 0:
                            header_label = 'createcurriculums';
                        break;
                        default:
                            header_label = 'updatecurriculum';
                        break;
                    }
                break;
                case 'session_form':
                    switch (args.id) {
                        case 0:
                            header_label = 'addsession';
                        break;
                        default:
                            header_label = 'updatesession';
                        break;
                    }
                break;
                case 'course_form':
                    switch (args.id) {
                        case 0:
                            header_label = 'addcourses';
                        break;
                        default:
                            header_label = 'updatecourses';
                        break;
                    }
                break;
                case 'program_manageprogram_form':
                    switch (args.id) {
                        case 0:
                            header_label = 'addprogram';
                        break;
                        default:
                            header_label = 'updateprogram';
                        break;
                    }
                break;
                case 'curriculum_completion_form':
                    header_label = 'curriculum_completion_settings';
                break;
                case 'curriculum_managesemester_form':
                 switch (args.id) {
                        case 0:
                            header_label = 'addsemester';
                        break;
                        default:
                            header_label = 'edit_level';
                        break;
                    }
                break;
                case 'curriculum_manageyear_form':
                  switch (args.id) {
                        case 0:
                            header_label = 'addyear';
                        break;
                        default:
                            header_label = 'updateyear';
                        break;
                    }
                break;
            }
        return Str.get_string(header_label, 'local_curriculum').then(function(title) {
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

            this.modal.getRoot().addClass('openLMStransition local_curriculum');

            // We want to reset the form every time it is opened.
            this.modal.getRoot().on(ModalEvents.hidden, function() {
                    modal.destroy();
            }.bind(this));
            this.modal.getFooter().find('[data-action="save"]').on('click', this.submitForm.bind(this));
            this.modal.getFooter().find('[data-action="cancel"]').on('click', function() {
                window.location.href =  window.location.href+String();
            });
            this.modal.getFooter().find('[data-action="skip"]').on('click', function() {
                self.args.form_status = self.args.form_status + 1;
                 if (args.callback == 'program_form') {
                 }
                var data = self.getBody();
                modal.setBody(data);
            });

            this.modal.getRoot().on('submit', 'form', function(form) {
                self.submitFormAjax(form, self.args);
            });
            this.modal.show();
            this.modal.getRoot().animate({"right":"0%"}, 500);
            $(".close").click(function(){
                window.location.href =  window.location.href+String();
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
     * @return {Promise}
     */
    AjaxForms.prototype.getFooter = function() {
        var footer;
         if(this.args.callback == "course_form"){
          footer = '<button type="button" class="btn btn-primary" data-action="save">Assign</button>&nbsp;';
        }
        else{
        if(this.args.id){
             footer = '<button type="button" class="btn btn-primary" data-action="save">Save changes</button>&nbsp;';
        }
        else{
        footer = '<button type="button" class="btn btn-primary" data-action="save">Save changes</button>&nbsp;';
        }
        if (this.args.form_status == 0) {
            var style = 'style="display:none;"';
            footer += '<button type="button" class="btn btn-secondary" data-action="skip" ' + style + '>Skip</button>&nbsp;';
        }
       }
        footer += '<button type="button" class="btn btn-secondary" data-action="cancel">Cancel</button>';
        return footer;
    };
    /**
     * @method handleFormSubmissionResponse
     * @private
     * @return {Promise}
     */
    AjaxForms.prototype.handleFormSubmissionResponse = function() {
        this.modal.destroy();
        // We could trigger an event instead.
        // Yuk.
        Y.use('moodle-core-formchangechecker', function() {
            M.core_formchangechecker.reset_form_dirty_state();
        });
        var params = this.args;
        var semester = params.semesterid;
        if(params.pluginname == "curriculum_addyear"){
            var yearid = params.id;
        }else{
            var yearid = params.yearid;
        }
                if(params.yearid){
                $.ajax({
                    method: 'POST',
                    url: M.cfg.wwwroot + '/local/curriculum/ajax.php',
                    data: {
                        action: 'curriculumyearsemesters',
                        curriculumid: params.curriculumid,
                        yearid: yearid,
                    },
                    success:function(resp){
                        $('.yearstabscontent_container').html(resp);
            if(semester){
                if($('.tab-content .local_program-semisters_wrap #lpcourse_content'+semester+' #semcollapse_'+semester+'')
                    .hasClass('collapse')){
                    if($('.tab-content .local_program-semisters_wrap .lpcourse_content:first-child .courseslist')
                        .hasClass('collapse')){
                         $('.tab-content .local_program-semisters_wrap .lpcourse_content:first-child .semcontentlist.semabove')
                         .addClass('collapsed');
                         $('.tab-content .local_program-semisters_wrap .lpcourse_content:first-child .courseslist')
                         .removeClass('collapse in').addClass('collapse');
                    }
                         $('.tab-content .local_program-semisters_wrap #lpcourse_content'+semester+' #semcontentlist'+semester+'')
                                .attr("aria-expanded","true");
                         $('.tab-content .local_program-semisters_wrap #lpcourse_content'+semester+' #semcollapse_'+semester+'')
                         .removeClass('collapse').addClass('collapse in');
                }
            }else{
                            if($('.tab-content .local_program-semisters_wrap .lpcourse_content:first-child .courseslist')
                                .hasClass('collapse')){
                                $('.tab-content .local_program-semisters_wrap .lpcourse_content:first-child .courseslist')
                                .removeClass('collapse').addClass('collapse in');
                            }
            }
                    }
                });
            }
            else{
                document.location.reload();
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
     * @param {object} args submission event.
     */
    AjaxForms.prototype.submitFormAjax = function(e, args) {
        // We don't want to do a real form submission.
        e.preventDefault();
        // Convert all the form elements values to a serialised string.
        var formData = this.modal.getRoot().find('form').serialize();
        var methodname = args.plugintype + '_' + args.pluginname + '_submit_data';
        // Now we can continue...
        var params = {};
        params.contextid = args.contextid;
        params.id = args.id;
        params.yearid = args.yearid;
        params.jsonformdata = JSON.stringify(formData);
        Ajax.call([{
            methodname: methodname,
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
         * @param {string} args The CSS selector used to find nodes that will trigger this module.
         * @return {Promise}
         */
          course_switch: function(args) {
            var checkbox_value;
            var curriculumid = args.curriculumid;
            var semesterid = args.semesterid;

                if($('#switch_course_'+args.courseid).is(':checked')){
                   var switch_type = 1;
                   checkbox_value = $('#switch_course_'+args.courseid).val();
                }else{
                    var switch_type = 0;
                    checkbox_value = $('#switch_course_'+args.courseid).val();
                }
                $.ajax({
                type: 'POST',
                url: M.cfg.wwwroot + '/local/curriculum/ajax.php?course='+checkbox_value+
                                    '&switch_type='+switch_type+'&curriculumid='+curriculumid+'&semesterid='+semesterid,
                data: {action:''},
                success: function() {
                    if(switch_type == 1){
                        $('#notifycompletion'+checkbox_value).show();
                    }else{
                        $('#notifycompletion'+checkbox_value).hide();
                    }
                },
                error: function() {
                },
                complete: function() {

                }
                });
        },
        init: function(args) {
            return new AjaxForms(args);
        },
        load: function () {
              $(document).on('change', '#id_costcenter', function() {
                var costcentervalue = $(this).find("option:selected").val();
                if (costcentervalue !== null) {
                    $.ajax({
                        method: "GET",
                        dataType: "json",

                        url: M.cfg.wwwroot + "/local/curriculum/ajax.php?action=departmentlist&costcenter="+costcentervalue,
                        success: function(data){
                            var template = '<option value= 0>Select Program</option>';
                            $.each(data, function( index, value) {
                            template +=  '<option value = ' + value.id + ' >' +value.name + '</option>';
                            });
                            $("#id_programid").html(template);
                        }
                    });
                }
            });
        },
        myDescFunction: function() {
                var dots = document.getElementById('descriptiondotsBtn');
                var moreText = document.getElementById('resttextDisplay');
                var btnText = document.getElementById('readmoremyBtn');
                if (dots.style.display === "none") {
                    dots.style.display = "inline";
                    btnText.innerHTML = "Read more";
                    moreText.style.display = "none";
                  } else {
                    dots.style.display = "none";
                    btnText.innerHTML = "Read less";
                    moreText.style.display = "inline";
                }
            },
              curriculumDatatable: function(args) {
        var params = [];
            params.action = 'viewcurriculums';
            params.curriculumstatus = args.curriculumstatus;
              $.ajax({
                type: "POST",
               url:   M.cfg.wwwroot + '/local/curriculum/ajax.php',
                data: {action:'viewcurriculums',
                    sesskey: M.cfg.sesskey
                },
            });
        },
        deleteConfirm: function(args) {
            return Str.get_strings([{
                key: 'confirmation',
                component: 'local_curriculum'
            },
            {
                key: 'yes',
            },
            {
                key: 'deleteallconfirm',
                component: 'local_curriculum'
            },
            {
                key: 'delete',
                component: 'local_curriculum'
            },
            {
                key: 'deletecourseconfirm',
                component: 'local_curriculum'
            },
            {
                key: 'cannotdeletecurriculum',
                component: 'local_curriculum',
                param: args.curriculumname,
            },
            {
                key: 'cannotdeletesession',
                component: 'local_curriculum'
            },
            {
                key: 'cannotdeletesemester',
                component: 'local_curriculum'
            },

            {
                key: 'cannotdeletesemesteryear',
                component: 'local_curriculum'
            },
            {
                key: 'confirmunassignfaculty',
                component: 'local_curriculum'
            },
            {
                key: 'deleteconfirm',
                component: 'local_curriculum',
                param: args.curriculumname,
            }]).then(function(s) {
                if (args.action == "deletecurriculum") {
                    s[1] = s[10];
                    var confirm = ModalFactory.types.SAVE_CANCEL;
                 } else if (args.action == "deletecurriculumcourse") {
                    s[1] = s[4];
                    var confirm = ModalFactory.types.SAVE_CANCEL;
                 } else if (args.action == "cannotdeletecurriculum") {
                    s[1] = s[5];
                    var confirm = ModalFactory.types.DEFAULT;
                 } else if (args.action == "cannotdeletesession") {
                    s[1] = s[6];
                    var confirm = ModalFactory.types.DEFAULT;
                 } else if (args.action == "cannotdeletesemester") {
                    s[1] = s[7];
                    var confirm = ModalFactory.types.DEFAULT;
                 }else if (args.action == "cannotdeletesemesteryear") {
                    s[1] = s[8];
                    var confirm = ModalFactory.types.SAVE_CANCEL;
                 }else if (args.action == "unassignfaculty") {
                    s[1] = s[9];
                    var confirm = ModalFactory.types.SAVE_CANCEL;
                 } else {
                    s[1] = s[2];
                    var confirm = ModalFactory.types.SAVE_CANCEL;
                 }
                ModalFactory.create({
                    title: s[0],
                    type: confirm,
                    body: s[1]
                }).done(function(modal) {
                    this.modal = modal;
                    if(args.action != "cannotdeletecurriculum" && args.action != "cannotdeletesession"
                        && args.action != "cannotdeletesemester" && args.action != "cannotdeletesemesteryear"){
                        modal.setSaveButtonText(s[3]);
                    }
                    modal.getRoot().on(ModalEvents.save, function(e) {
                        e.preventDefault();
                        args.confirm = true;
                        var promise = Ajax.call([{
                            methodname: 'local_curriculum_' + args.action,
                            args: args
                        }]);
                        promise[0].done(function() {
                            if(args.action == "deletecurriculum"){
                                window.location.href = M.cfg.wwwroot + '/local/curriculum/index.php';
                            } else {
                                window.location.href = M.cfg.wwwroot + '/local/curriculum/view.php?ccid='
                                + args.curriculumid +'&type=1';
                            }
                        }).fail(function() {
                        });
                    }.bind(this));
                    modal.show();
                }.bind(this));
            }.bind(this));
        },

        unassignCourses: function(args){
            return Str.get_strings([{
                    key: 'confirm'
                },
                {
                    key: 'yes',
                },
                {
                    key: 'unassign',
                    component:'local_curriculum',
                },
                {
                    key: 'cannotunassign_courses_confirm',
                    component:'local_curriculum',
                },
                {
                    key: 'unassign_courses_confirm',
                    component: 'local_curriculum',
                    param : args
                }]).then(function(s) {
                    if (args.action == "unassign_course") {
                        s[1] = s[4];
                        var confirm = ModalFactory.types.SAVE_CANCEL;
                    } else if (args.action == "cannotunassign_course") {
                        s[1] = s[3];
                        var confirm = ModalFactory.types.DEFAULT;
                    } else {
                         s[1] = s[0];
                        var confirm = ModalFactory.types.SAVE_CANCEL;
                    }
                    ModalFactory.create({
                        title: s[0],
                        type: confirm,
                        body: s[1]
                    }).done(function(modal) {
                        this.modal = modal;
                        if (args.action != "cannotunassign_course") {
                            modal.setSaveButtonText(s[2]);
                        }
                        modal.getRoot().on(ModalEvents.save, function(e) {
                            e.preventDefault();
                            var params = {};
                            params.programid = args.programid;
                            params.curriculumid = args.curriculumid;
                            params.semesterid = args.semesterid;
                            params.yearid = args.yearid;
                            params.courseid = args.courseid;
                            var promise = Ajax.call([{
                                methodname: 'local_curriculum_' + args.action,
                                args: params
                            }]);
                            promise[0].done(function() {
                                modal.hide();
                                var semester = params.semesterid;
                                $.ajax({
                                    method: 'POST',
                                    url: M.cfg.wwwroot + '/local/curriculum/ajax.php',
                                    data: {
                                        action: 'curriculumyearsemesters',
                                        curriculumid: params.curriculumid,
                                        yearid: params.yearid,
                                    },
                                    success:function(resp){
                                        $('.yearstabscontent_container').html(resp);
                if(semester){
                   if($('.tab-content .local_program-semisters_wrap #lpcourse_content'+semester+' #semcollapse_'+semester+'')
                                                .hasClass('collapse')){
                     if($('.tab-content .local_program-semisters_wrap .lpcourse_content:first-child .courseslist')
                        .hasClass('collapse')){
                        $('.tab-content .local_program-semisters_wrap .lpcourse_content:first-child .semcontentlist.semabove')
                        .addClass('collapsed');
                        $('.tab-content .local_program-semisters_wrap .lpcourse_content:first-child .courseslist')
                        .removeClass('collapse in').addClass('collapse');
                     }
                        $('.tab-content .local_program-semisters_wrap #lpcourse_content'+semester+' #semcontentlist'+semester+'')
                        .attr("aria-expanded","true");
                        $('.tab-content .local_program-semisters_wrap #lpcourse_content'+semester+' #semcollapse_'+semester+'')
                        .removeClass('collapse').addClass('collapse in');
                   }
                }else{
                     if($('.tab-content .local_program-semisters_wrap .lpcourse_content:first-child .courseslist')
                        .hasClass('collapse')){
                        $('.tab-content .local_program-semisters_wrap .lpcourse_content:first-child .courseslist')
                        .removeClass('collapse').addClass('collapse in');
                     }
                }
                                    }
                                });
                            }).fail(function() {
                            });
                        }.bind(this));
                        modal.show();
                    }.bind(this));
                modal.show();
            }.bind(this));
        },
    };
});
