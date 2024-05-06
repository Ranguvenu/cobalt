YUI.add('moodle-local_timetable-timetable', function(Y) {
    var ModulenameNAME = 'timetable';
    var timetable = function() {
        timetable.superclass.constructor.apply(this, arguments);
    };
    Y.extend(timetable, Y.Base, {
        initializer : function(config) { // 'config' contains the parameter values
	
		  if (config && config.formid) {
		
                //using this for enabling the credit hour settings for a class
                var updatebut = Y.one('#'+config.formid+' #id_updatecourseformat');
                var formatselect1 = Y.one('#'+config.formid+' #id_schoolid');
                var semid = Y.one('#'+config.formid+' #id_semesterid');
                var classid = Y.one('#'+config.formid+' #id_classid');
		//var classtypeid = Y.one('#'+config.formid+' #id_classtype');
	//formatselect1.append('<img src="burg_bun_top.png"/>');
                updatebut.setStyle('display','none');
                if (formatselect1) {
		       formatselect1.on('change', function() {
			formatselect1.insert('<img src="ajax-loader.gif" class="reloadicon"/>', 'after');
                       updatebut.simulate('click');
                    });
                }
		if (semid) {
		       semid.on('change', function() {
                       updatebut.simulate('click');
                    });
                }
                if (classid) {
		       classid.on('change', function() {
                       updatebut.simulate('click');
                    });
                }
//                 if (classtypeid) {
//		       classtypeid.on('change', function() {
//                       updatebut.simulate('click');
//                    });
//                }
                
		
		
            }
        }
    });
    M.local_timetable = M.local_timetable || {}; // This line use existing name path if it exists, ortherwise create a new one. 
                                                 // This is to avoid to overwrite previously loaded module with same name.
    M.local_timetable.init_timetable = function(config) { // 'config' contains the parameter values
        
        return new timetable(config); // 'config' contains the parameter values
    }
  }, '@VERSION@', {
      requires:['base', 'node', 'node-event-simulate']
  });