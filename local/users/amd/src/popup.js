/**
 * Add a create new group modal to the page.
 *
 * @module     local_courses/newcourse
 * @class      NewCourse
 * @package
 * @copyright  2017 Shivani
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

define(['local_users/jquery.dataTables', 'jquery', 'core/str', 'core/modal_factory', 'core/modal_events',
        'core/fragment', 'core/ajax', 'core/yui', 'jqueryui'],
        function(DataTable, $, Str, ModalFactory, ModalEvents, Fragment) {
    /**
     * Constructor
     * @param {String} args used to find triggers for the new group modal.
     * Each call to init gets it's own instance of this class.
     */
    var NewPopup = function(args) {
        this.contextid = args.contextid;
        this.id = args.id;
        var self = this;
        self.init(args.selector);
        // console.log(self);
    };
    // console.log(args);
    /**
     * @var {Modal} modal
     * @private
     */
    NewPopup.prototype.modal = null;
    /**
     * @var {int} contextid
     * @private
     */
    NewPopup.prototype.contextid = -1;
    /**
     * Initialise the class.
     *
     * @private
     * @return {Promise}
     */
    NewPopup.prototype.init = function() {
        var self = this;

         $(document).on('click', '#userprogrampopup', function(){
            self.programid = $(this).data('programid');
            self.userid = $(this).data('userid');
  
            Str.get_string('programdetails', 'local_users', self).then(function(title) {
            
                ModalFactory.create({
                  //  type: ModalFactory.types.SAVE_CANCEL,
                    title: title,
                    body: self.ugetBody()
                }).done(function(modal) {
                    // Keep a reference to the modal.
                    self.modal = modal;
                    // Forms are big, we want a big modal.
                    self.modal.setLarge();
         
                    // We want to reset the form every time it is opened.
                    self.modal.getRoot().on(ModalEvents.hidden, function() {
                        // self.modal.setBody('');
                        self.modal.hide();
                        self.modal.destroy();
                    }.bind(this));

                    // We want to reset the form every time it is opened.
                    self.modal.getRoot().on(ModalEvents.cancel, function() {
                        // self.modal.setBody('');
                        self.modal.hide();
                        self.modal.destroy();
                    }.bind(this));
                    self.modal.show();

                    self.modal.getRoot().on(ModalEvents.bodyRendered, function() {
                         self.dataTableshow(self.programid);
                    }.bind(this));                                    
                                  
                });    

            });
        });

        $(document).on('click', '.rolesuserpopupteacher', function(){
            // console.log($(this));
            self.id = $(this).data('id');
            Str.get_string('facultydetails', 'local_users', self).then(function(title) {
                ModalFactory.create({
                    // type: ModalFactory.types.CANCEL,
                    title: title,
                    body: self.getBody()
                }).done(function(modal) {
                    // Keep a reference to the modal.
                    self.modal = modal;

                    // Forms are big, we want a big modal.
                    self.modal.setSmall();

                    // We want to reset the form every time it is opened.
                    self.modal.getRoot().on(ModalEvents.hidden, function() {
                        self.modal.hide();
                        self.modal.destroy();
                    }.bind(this));

                    // We want to reset the form every time it is opened.
                    self.modal.getRoot().on(ModalEvents.cancel, function() {
                        self.modal.hide();
                        self.modal.destroy();
                    }.bind(this));
                    self.modal.show();

                    self.modal.getRoot().on(ModalEvents.bodyRendered, function() {
                         //  self.dataTableshow();
                    }.bind(this));

                });

             });
            });
        $(document).on('click', '.rolesuserpopup', function(){
            self.id = $(this).data('id');
            Str.get_string('coursedetails', 'local_users', self).then(function(title) {
                ModalFactory.create({
                    // type: ModalFactory.types.CANCEL,
                    title: title,
                    body: self.pgetBody()
                }).done(function(modal) {
                    // Keep a reference to the modal.
                    self.modal = modal;

                    // Forms are big, we want a big modal.
                    self.modal.setLarge();

                    // We want to reset the form every time it is opened.
                    self.modal.getRoot().on(ModalEvents.hidden, function() {
                        self.modal.hide();
                        self.modal.destroy();
                    }.bind(this));

                    // We want to reset the form every time it is opened.
                    self.modal.getRoot().on(ModalEvents.cancel, function() {
                        self.modal.hide();
                        self.modal.destroy();
                    }.bind(this));
                    self.modal.show();

                    self.modal.getRoot().on(ModalEvents.bodyRendered, function() {
                      self.dataTableshows();
                    }.bind(this));

                });
            });
            });
        $(document).on('click', '.rolesuserpopupsems', function(){
            // console.log($(this));
            self.id = $(this).data('id');
            Str.get_string('semdetails', 'local_users', self).then(function(title) {
                ModalFactory.create({
                    // type: ModalFactory.types.CANCEL,
                    title: title,
                    body: self.semgetBody()
                }).done(function(modal) {
                    // Keep a reference to the modal.
                    self.modal = modal;

                    // Forms are big, we want a big modal.
                    self.modal.setLarge();

                    // We want to reset the form every time it is opened.
                    self.modal.getRoot().on(ModalEvents.hidden, function() {
                        self.modal.hide();
                        self.modal.destroy();
                    }.bind(this));

                    // We want to reset the form every time it is opened.
                    self.modal.getRoot().on(ModalEvents.cancel, function() {
                        self.modal.hide();
                        self.modal.destroy();
                    }.bind(this));
                    self.modal.show();

                    self.modal.getRoot().on(ModalEvents.bodyRendered, function() {
                         // self.dataTableshow();
                    }.bind(this));

                });

            });
            });
        $(document).on('click', '.rolesuserpopuptopics', function(){
            self.id = $(this).data('id');
            Str.get_string('topicdetails', 'local_users', self).then(function(title) {
                ModalFactory.create({
                    // type: ModalFactory.types.CANCEL,
                    title: title,
                    body: self.topicsgetBody()
                }).done(function(modal) {
                    // Keep a reference to the modal.
                    self.modal = modal;

                    // Forms are big, we want a big modal.
                    self.modal.setSmall();

                    // We want to reset the form every time it is opened.
                    self.modal.getRoot().on(ModalEvents.hidden, function() {
                        self.modal.hide();
                        self.modal.destroy();
                    }.bind(this));

                    // We want to reset the form every time it is opened.
                    self.modal.getRoot().on(ModalEvents.cancel, function() {
                        self.modal.hide();
                        self.modal.destroy();
                    }.bind(this));
                    self.modal.show();

                    self.modal.getRoot().on(ModalEvents.bodyRendered, function() {
                         // self.dataTableshow();
                    }.bind(this));

                });
            });
            });
        $(document).on('click', '.rolesuserpopupsemcourse', function(){
            // console.log($(this));
            self.id = $(this).data('id');
            Str.get_string('semcoursedetails', 'local_users', self).then(function(title) {
                ModalFactory.create({
                    // type: ModalFactory.types.CANCEL,
                    title: title,
                    body: self.semcoursegetBody()
                }).done(function(modal) {
                    // Keep a reference to the modal.
                    self.modal = modal;

                    // Forms are big, we want a big modal.
                    self.modal.setSmall();

                    // We want to reset the form every time it is opened.
                    self.modal.getRoot().on(ModalEvents.hidden, function() {
                        self.modal.hide();
                        self.modal.destroy();
                    }.bind(this));

                    // We want to reset the form every time it is opened.
                    self.modal.getRoot().on(ModalEvents.cancel, function() {
                        self.modal.hide();
                        self.modal.destroy();
                    }.bind(this));
                    self.modal.show();

                    self.modal.getRoot().on(ModalEvents.bodyRendered, function() {
                         // self.dataTableshow();
                    }.bind(this));

                });
              });
            });
    };


   NewPopup.prototype.dataTableshow = function(batchid){

        Str.get_strings([{
            key: 'nodata_available',
            component: 'local_groups',
        },
        {
            key: 'search',
            component: 'local_groups',
        }
        ]).then(function(s) {

            $('#popup_user'+batchid).DataTable({

                'bPaginate': true,
                'bFilter': true,
                'bLengthChange': true,

                 "bInfo" : false,
                 "paging": true,
                'lengthMenu': [
                    [5, 10, 25, 50, 100, -1],
                    [5, 10, 25, 50, 100, 'All']
                ],
                'language': {
                    'emptyTable': s[0],
                    'infoEmpty': s[0],
                    'zeroRecords': s[0],
                    'paginate': {
                        'previous': '<',
                        'next': '>'
                    }
                },
                "oLanguage": {
                    "sSearch": s[1]
                },

                'bProcessing': true,
                "stateSave": true,
                 "bDestroy": true
            });

 
        }.bind(this));
        // $.fn.dataTable.ext.errMode = 'none';
    };

      NewPopup.prototype.dataTableshows = function(){

        Str.get_strings([{
            key: 'nodata_available',
            component: 'local_groups',
        },
        {
            key: 'search',
            component: 'local_groups',
        }
        ]).then(function(s) {

            $('#local_course_data').DataTable({

                'bPaginate': true,
                'bFilter': true,
                'bLengthChange': true,

                 "bInfo" : false,
                 "paging": true,
                'lengthMenu': [
                    [5, 10, 25, 50, 100, -1],
                    [5, 10, 25, 50, 100, 'All']
                ],
                'language': {
                    'emptyTable': s[0],
                    'infoEmpty': s[0],
                    'zeroRecords': s[0],
                    'paginate': {
                        'previous': '<',
                        'next': '>'
                    }
                },
                "oLanguage": {
                    "sSearch": s[1]
                },

                'bProcessing': true,
                "stateSave": true,
                 "bDestroy": true
            });
        }.bind(this));
        // $.fn.dataTable.ext.errMode = 'none';
    };
    /**
     * @method getBody
     * @private
     * @param {object} formdata
     * @return {Promise}
     */
    NewPopup.prototype.getBody = function(formdata) {
        if (typeof formdata === "undefined") {
            formdata = {};
        }
        // Get the content of the modal.
        if(typeof this.id != 'undefined'){
            var params = {id:this.id, jsonformdata: JSON.stringify(formdata)};
        }else{
            var params = {};
        }
        return Fragment.loadFragment('local_users', 'teacher_display', this.contextid, params);
    };


    NewPopup.prototype.pgetBody = function(formdata) {
        if (typeof formdata === "undefined") {
            formdata = {};
        }
        // Get the content of the modal.
        if(typeof this.id != 'undefined'){
            var params = {id:this.id, jsonformdata: JSON.stringify(formdata)};
        }else{
            var params = {};
        }
        return Fragment.loadFragment('local_users', 'users_display', this.contextid, params);
    };
    NewPopup.prototype.semgetBody = function(formdata) {
        if (typeof formdata === "undefined") {
            formdata = {};
        }
        // Get the content of the modal.
        if(typeof this.id != 'undefined'){
            var params = {id:this.id, jsonformdata: JSON.stringify(formdata)};
        }else{
            var params = {};
        }
        return Fragment.loadFragment('local_users', 'sems_display', this.contextid, params);
    };
    NewPopup.prototype.topicsgetBody = function(formdata) {
        if (typeof formdata === "undefined") {
            formdata = {};
        }
        // Get the content of the modal.
        if(typeof this.id != 'undefined'){
            var params = {id:this.id, jsonformdata: JSON.stringify(formdata)};
        }else{
            var params = {};
        }
        return Fragment.loadFragment('local_users', 'topics_display', this.contextid, params);
    };
    NewPopup.prototype.semcoursegetBody = function(formdata) {
        if (typeof formdata === "undefined") {
            formdata = {};
        }
        // Get the content of the modal.
        if(typeof this.id != 'undefined'){
            var params = {id:this.id, jsonformdata: JSON.stringify(formdata)};
        }else{
            var params = {};
        }
        return Fragment.loadFragment('local_users', 'semcourse_display', this.contextid, params);
    };

     NewPopup.prototype.ugetBody = function(formdata) {

        if (typeof formdata === "undefined") {
            formdata = {};
        }
        // Get the content of the modal.
        if(typeof this.programid != 'undefined'){
            var params = {programid:this.programid,userid:this.userid, jsonformdata: JSON.stringify(formdata)};
        }else{
            var params = {};
        }
      
        return Fragment.loadFragment('local_users', 'program_display', this.contextid, params);
    };


    return /** @alias module:local_evaluation/newevaluation */ {
        // Public variables and functions.
        /**
         * Attach event listeners to initialise this module.
         *
         * @method init
         * @param {string} args The CSS selector used to find nodes that will trigger this module.
         * @return {Promise}
         */
        init: function(args) {
            this.Datatable();
            return new NewPopup(args);
        },
        Datatable: function() {
        },
    };
});
