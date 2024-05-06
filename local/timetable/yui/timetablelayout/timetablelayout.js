YUI.add('moodle-local_timetable-timetablelayout', function (Y) {
    var ModulenameNAME = 'timetablelayout';
    var timetablelayout = function () {
        timetablelayout.superclass.constructor.apply(this, arguments);
    };
    Y.extend(timetablelayout, Y.Base, {
        initializer: function (config) { // 'config' contains the parameter values

            if (config && config.formid) {
                //using this for enabling the credit hour settings for a class
                var updatebut = Y.one('#' + config.formid + ' #id_updatecourseformat');
                var formatselect1 = Y.one('#' + config.formid + ' #id_schoolid');
                var programid = Y.one('#' + config.formid + ' #id_programid');
                var curriculumid = Y.one('#' + config.formid + ' #id_curriculumid');
                var batchid = Y.one('#' + config.formid + ' #id_batchid');
                var date = Y.one('#' + config.formid + ' #id_sessiondate_day');
                var month = Y.one('#' + config.formid + ' #id_sessiondate_month');
                var year = Y.one('#' + config.formid + ' #id_sessiondate_year');
                var courseid = Y.one('#' + config.formid + ' #id_coursesid');
                
                updatebut.setStyle('display', 'none');
                if (formatselect1) {
                    formatselect1.on('change', function () {
                        formatselect1.insert('<img src="ajax-loader.gif" class="reloadicon"/>', 'after');
                        updatebut.simulate('click');
                    });

                }
                if (programid) {
                    programid.on('change', function () {
                        updatebut.simulate('click');
                    });
                }
                if (batchid) {
                    batchid.on('change', function () {
                        updatebut.simulate('click');
                    });
                }
                if (date) {
                    date.on('change', function () {
                        updatebut.simulate('click');
                    });
                }
                if (month) {
                    month.on('change', function () {
                        updatebut.simulate('click');
                    });
                }
                if (year) {
                    year.on('change', function () {
                        updatebut.simulate('click');
                    });
                }
                if (courseid) {
                    courseid.on('change', function () {
                        updatebut.simulate('click');
                    });
                }
            }
        }
    });
    M.local_timetable = M.local_timetable || {}; // This line use existing name path if it exists, ortherwise create a new one. 
    // This is to avoid to overwrite previously loaded module with same name.
    M.local_timetable.init_timetablelayout = function (config) { // 'config' contains the parameter values

        return new timetablelayout(config); // 'config' contains the parameter values
    }
}, '@VERSION@', {
    requires: ['base', 'node', 'node-event-simulate']
});