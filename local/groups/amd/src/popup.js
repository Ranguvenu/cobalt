/**
 * Add a create new group modal to the page.
 *
 * @module     local_batch/Batch
 * @class      Batch
 * @copyright  2023 Dipanshu
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
define(['local_groups/jquery.dataTables', 'jquery', 'core/str', 'core/modal_factory', 'core/modal_events',
        'core/fragment', 'core/ajax', 'core/yui', 'jqueryui'],
        function(DataTable, $, str, ModalFactory, ModalEvents, Fragment, Ajax, Y) {

    /**
     * Constructor
     *
     * @param {String} selector used to find triggers for the new group modal.
     * @param {int} contextid
     *
     * Each call to init gets it's own instance of this class.
     */
    var NewPopup = function(args) {
        this.contextid = args.contextid;
        var self = this;
        self.init(args.selector);
    };
 
    /**
     * @var {Modal} modal
     * @private
     */
  // NewPopup.prototype.modal = null;
 
    /**
     * @var {int} contextid
     * @private
     */
 //   NewPopup.prototype.contextid = -1;
 
    /**
     * Initialise the class.
     *
     * @param {String} selector used to find triggers for the new group modal.
     * @private
     * @return {Promise}
     */
    NewPopup.prototype.init = function(selector) {
        var self = this;

        // program popup.
        $(document).on('click', '#batchuserpopup', function(){

            self.batchid = $(this).data('batchid');
            self.action = $(this).data('action');
          
            str.get_string('udetails', 'local_groups').then(function(title) {
            
                ModalFactory.create({
                  //  type: ModalFactory.types.SAVE_CANCEL,
                    title: title,
                    body: self.getBody()
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
                        // window.location.reload();
                    }.bind(this));

                    // We want to reset the form every time it is opened.
                    self.modal.getRoot().on(ModalEvents.cancel, function() {
                        // self.modal.setBody('');
                        self.modal.hide();
                        self.modal.destroy();
                    }.bind(this));
                    self.modal.show();

                    self.modal.getRoot().on(ModalEvents.bodyRendered, function() {
                        self.dataTableshow();
                    }.bind(this));
                });

            });
        });

        // semester popup.
        $(document).on('click', '#batchsemesterpopup', function(){

            self.roleid = $(this).data('roleid');
            self.username = $(this).data('username');
            self.groupid = $(this).data('batchid');

            str.get_string('semdetails', 'local_groups', self.username).then(function(title) {
                ModalFactory.create({
                  //  type: ModalFactory.types.SAVE_CANCEL,
                    title: title,
                    body: self.semgetBody()
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
                        self.semdataTableshow();
                    }.bind(this));
                });
            });
        });

        // index tab program popup.
        $(document).on('click', '#batchprogrampopup', function(){
            self.roleid = $(this).data('roleid');
            self.batchid = $(this).data('batchid');

            str.get_string('pdetails', 'local_groups').then(function(title) {

                ModalFactory.create({
                  //  type: ModalFactory.types.SAVE_CANCEL,
                    title: title,
                    body: self.pgetBody()
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
                        self.coursedataTableshow();
                    }.bind(this));
                });
            });
        });

        // courses popup.
        $(document).on('click', '#batchprogresspopup', function(){

            self.roleid = $(this).data('roleid');
            self.levelid = $(this).data('levelid');
            self.username = $(this).data('username');

            str.get_string('cdetails', 'local_groups', self.username).then(function(title) {
                ModalFactory.create({
                    //  type: ModalFactory.types.SAVE_CANCEL,
                    title: title,
                    body: self.progressgetBody()
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
                        self.dataTableshows();
                    }.bind(this));
                });
            });
        });
    };

    // Program dataTable.
    NewPopup.prototype.dataTableshow = function(){
        str.get_strings([{
            key: 'nousers_available',
            component: 'local_groups',
        },
        {
            key: 'search',
            component: 'local_groups',
        }
        ]).then(function(s) {

            var user_course_data = $('.class_local_program_user_course_data').DataTable({

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
                    "sZeroRecords": s[0],
                    "sSearch": s[1]
                },

                'bProcessing': true,
                "stateSave": true,
                "bDestroy": true,
            });
            user_course_data.search('').draw();

        }.bind(this));
        // $.fn.dataTable.ext.errMode = 'none';
    };

    // Semester dataTable.
    NewPopup.prototype.semdataTableshow = function(){
        str.get_strings([{
            key: 'nosem_available',
            component: 'local_groups',
        },
        {
            key: 'search',
            component: 'local_groups',
        }
        ]).then(function(s) {

            var local_semester_data = $('.class_local_semester_data').DataTable({

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
                    "sZeroRecords": s[0],
                    "sSearch": s[1]
                },

                'bProcessing': true,
                "stateSave": true,
                "bDestroy": true
            });
            local_semester_data.search('').draw();
        }.bind(this));
        // $.fn.dataTable.ext.errMode = 'none';
    };

    // // Course dataTable.
    NewPopup.prototype.dataTableshows = function(){
        str.get_strings([{
            key: 'nocr_available',
            component: 'local_groups',
        },
        {
            key: 'search',
            component: 'local_groups',
        }
        ]).then(function(s) {
            var popup_progress_user = $('#popup_progress_user').DataTable({

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
                    "sZeroRecords": s[0],
                    "sSearch": s[1]
                },

                'bProcessing': true,
                "stateSave": true,
                "bDestroy": true
            });
            popup_progress_user.search('').draw();
        }.bind(this));
        // $.fn.dataTable.ext.errMode = 'none';
    };

    NewPopup.prototype.coursedataTableshow = function(){

        str.get_strings([{
            key: 'nousers_available',
            component: 'local_groups',
        },
        {
            key: 'search',
            component: 'local_groups',
        }
        ]).then(function(s) {

            var local_course_data = $('#local_course_data').DataTable({

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
                    "sZeroRecords": s[0],
                    "sSearch": s[1]
                },

                'bProcessing': true,
                "stateSave": true,
                "bDestroy": true
            });
            local_course_data.search('').draw();
 
        }.bind(this));
        // $.fn.dataTable.ext.errMode = 'none';
    };

    /**
     * @method getBody
     * @private
     * @return {Promise}
     */
    NewPopup.prototype.getBody = function(formdata) {

        if (typeof formdata === "undefined") {
            formdata = {};
        }
        // Get the content of the modal.
        if(typeof this.batchid != 'undefined'){
            var params = {batchid:this.batchid, jsonformdata: JSON.stringify(formdata)};
        }else{
            var params = {};
        }
        return Fragment.loadFragment('local_groups', 'batchuser_display', this.contextid, params);
    };

    NewPopup.prototype.pgetBody = function(formdata) {


        if (typeof formdata === "undefined") {
            formdata = {};
        }
        // Get the content of the modal.
       
        if(typeof this.roleid != 'undefined'){
            var params = {roleid:this.roleid,batchid:this.batchid, jsonformdata: JSON.stringify(formdata)};
        }else{
            var params = {};
        }
        return Fragment.loadFragment('local_groups', 'batchprogram_display', this.contextid, params);
    };


    NewPopup.prototype.progressgetBody = function(formdata) {

        if (typeof formdata === "undefined") {
            formdata = {};
        }
        // Get the content of the modal.
        if(typeof this.roleid != 'undefined'){
            var params = {roleid:this.roleid,levelid:this.levelid,  jsonformdata: JSON.stringify(formdata)};
        }else{
            var params = {};
        }
        return Fragment.loadFragment('local_groups', 'batchprogress_display', this.contextid, params);
    };

    NewPopup.prototype.semgetBody = function(formdata) {

        if (typeof formdata === "undefined") {
            formdata = {};
        }
        // Get the content of the modal.
        if(typeof this.roleid != 'undefined'){
            var params = {roleid:this.roleid,  jsonformdata: JSON.stringify(formdata)};
        }else{
            var params = {};
        }
        return Fragment.loadFragment('local_groups', 'semesterrogress_display', this.contextid, params);
    };
 
 

 
 
    return /** @alias module:local_evaluation/newevaluation */ {
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
           
            this.Datatable();
            return new NewPopup(args);
        },
        Datatable: function() {
            
        },
        
    };
});
