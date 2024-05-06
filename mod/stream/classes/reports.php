<?php
namespace mod_stream;
class reports {
	public function __construct(){
		global $DB;
		$this->db = $DB;
		$this->reportnames = array('connectedVideos', 'attemptsInfo', 'videoLikesRatings', 'videolikes', 'videoratings');
	}
	public function get_report_info($reportname, $videoid = '', $lastmodified = 0){
		if(in_array($reportname, $this->reportnames)){
			return $this->$reportname($videoid, $lastmodified);
		}else{
			throw new \moodle_exception(get_string('noreport', 'stream'));
		}
	}
	public function videoratings($videoid, $lastmodified){
		$sql = " SELECT sr.id, sr.userid, sr.rating, sr.timemodified, sr.timecreated, s.videoid 
			FROM {stream_rating} as sr 
			JOIN {stream} AS s ON s.id = sr.itemid 
			WHERE 1=1 ";
		if(!empty($videoid)){
			$sql .= " AND s.videoid = :videoid ";
			$params['videoid'] = $videoid;
		}
		if($lastmodified){
			$sql .= " AND (sr.timecreated > :timecreated OR sr.timemodified > :timemodified) ";
			$params['timecreated'] = $params['timemodified'] = $lastmodified;
		}
		return $this->db->get_records_sql($sql, $params);
	}
	public function videolikes($videoid, $lastmodified){
		$sql = " SELECT sl.id, sl.userid, sl.likestatus, sl.timecreated, sl.timemodified, s.videoid 
			FROM {stream_like} as sl 
			JOIN {stream} AS s ON s.id = sl.itemid 
			WHERE 1=1 ";
		if(!empty($videoid)){
			$sql .= " AND s.videoid = :videoid ";
			$params['videoid'] = $videoid;
		}
		if($lastmodified){
			$sql .= " AND (sl.timecreated > :timecreated OR sl.timemodified > :timemodified) ";
			$params['timecreated'] = $params['timemodified'] = $lastmodified;
		}
		return $this->db->get_records_sql($sql, $params);
	}
	public function connectedVideos($videoid = '', $lastmodified = 0){
		return $this->db->get_field_sql("SELECT count(distinct(id)) FROM {stream} WHERE 1=1 ");
	}
	public function attemptsInfo($videoid = '', $lastmodified = 0){

		$sql = "SELECT sa.*, s.videoid FROM {stream_attempts} AS sa
			JOIN {course_modules} AS cm ON cm.id = sa.moduleid
			JOIN {modules} m ON m.id = cm.module
			JOIN {stream} AS s ON s.id = cm.instance
			WHERE 1=1 AND m.name = 'stream'";
		$params = [];
		if(!empty($videoid)){
			$sql .= " AND s.videoid = :videoid ";
			$params['videoid'] = $videoid;
		}
		if($lastmodified){
			$sql .= " AND (sa.timecreated > :timecreated OR sa.timemodified > :timemodified) ";
			$params['timecreated'] = $params['timemodified'] = $lastmodified;
		}

		return $this->db->get_records_sql($sql, $params);
	}
	public function videoLikesRatings($videoid = '', $lastmodified = 0){
		$sql = "SELECT srl.*, s.videoid FROM {stream_ratings_likes} AS srl
			JOIN {stream} AS s ON s.id = srl.module_id
			WHERE 1=1 ";
		$params = [];
		if($videoid){
			$sql .= " AND s.videoid = :videoid ";
			$params['videoid'] = $videoid;
		}
		return $this->db->get_records_sql($sql, $params);
	}
	public function getSummaryReport($courseid=null, $params=null, $timefilter=null, $search=null){
		$sql = "SELECT s.id, cm.id as cmid, cm.course as courseid, s.name AS streamname, count(sa.id) as attempts, module_like as likes, round(module_rating, 1) as rating, round(((sum(sa.completedduration)/count(distinct(sa.userid)))/60),2) as averagetime, c.fullname as coursename , concat(u.firstname,' ',u.lastname) AS creatorname, s.timecreated, s.duration
			FROM {stream} AS s
			LEFT JOIN {course_modules} AS cm ON cm.instance = s.id 
				AND cm.module = (SELECT id FROM {modules} WHERE name LIKE 'stream')
			LEFT JOIN {stream_attempts} AS sa ON sa.moduleid = cm.id 
			LEFT JOIN {course} AS c ON c.id = s.course 
			LEFT JOIN {user} AS u ON u.id = s.usercreated
			LEFT JOIN {stream_ratings_likes} AS srl ON srl.module_id = s.id 
			WHERE 1 = 1 ";
		if(!is_null($courseid)  && $courseid > 1){
			$sql .= " AND cm.course =".  $courseid ."" ;
		}
        if($timefilter != ''){
        	$time = explode (",", $timefilter);
        	if(count($time) == 2 && $time[0]>0 && $time[1]>0) {
        		$sql .= " AND s.timecreated > {$time[0]} AND s.timecreated < {$time[1]} ";
			}
        }
        if($search != ''){
	    	$sql .= " AND s.name LIKE '%". $search ."%' OR c.fullname LIKE '%" . $search ."%' "; 
        }
		$sql .= " group by s.id";
     	$summary = $this->db->get_records_sql($sql, array(), $params["start"], $params["length"]);
		$summarycount = count($this->db->get_records_sql($sql));
		return compact('summary', 'summarycount');
	}
	public function viewsReport($id=null, $type=null, $params=null, $timefilter=null, $search=null){
		$sql = "SELECT sa.timecreated, CONCAT(u.firstname, ' ',  u.lastname) as fullname, u.id as userid, u.email, cm.id as cmid, s.name as stream, count(sa.id) as attempts, sa.percentage FROM {stream_attempts} sa
            JOIN {course_modules} cm ON cm.id = sa.moduleid
            JOIN {modules} m ON m.id = cm.module
            JOIN {stream} s ON s.id = cm.instance
            JOIN {user} u ON u.id = sa.userid
        WHERE u.confirmed = 1 AND m.name = 'stream' ";
        if($type == "course" && $id ) {
        	$sql .= " AND cm.course = {$id} ";
        } else if($type == "module" && $id) {
        	$sql .= " AND cm.id = {$id} ";
        }
        if($timefilter != ''){
        	$time = explode (",", $timefilter);
        	if(count($time) == 2 && $time[0]>0 && $time[1]>0) {
	        	$sql .= " AND sa.timecreated > {$time[0]} AND sa.timecreated < {$time[1]} ";
    		}
        }
        if($search != ''){
	    	$sql .= " AND CONCAT(u.firstname, u.lastname) LIKE '%". $search ."%' OR s.name LIKE '%". $search ."%' "; 
        }
        $sql .= " GROUP BY sa.userid, sa.moduleid ";

		$views = $this->db->get_records_sql($sql, array(), $params["start"], $params["length"]);
		$viewscount = count($this->db->get_records_sql($sql));
		$totalSql = "SELECT SUM(t.totalviews) FROM (SELECT MAX(attempt) as totalviews FROM {stream_attempts}
			WHERE 1 = 1 ";
        if($type == "course" && $id ) {
        	$totalSql .= " AND courseid = {$id} ";
        } else if($type == "module" && $id ) {
        	$totalSql .= " AND moduleid = {$id} ";
        }
    	$totalSql .= " GROUP BY userid,moduleid) as t " ;
        $totalviews = $this->db->get_field_sql($totalSql);
        return compact('views', 'viewscount', 'totalviews');
	}
	public function likesInfoReport($courseid=null, $moduleid = null, $params = []){
		$sql = "SELECT count(sl.id) FROM {stream_like} AS sl 
			JOIN {course_modules} AS cm ON cm.instance = sl.itemid AND cm.module = (SELECT id FROM {modules} WHERE name LIKE 'stream')
			WHERE 1=1 ";
		if(!is_null($moduleid)){
			$sql .= " AND cm.id = {$moduleid} ";
		}
		if(!is_null($courseid)){
        	$sql .= " AND cm.course = {$courseid} ";
        }
        if(isset($params['starttime']) && $params['starttime'] > 0){
        	$sql .= " AND sl.timemodified > {$params['starttime']} ";	
        }
        if(isset($params['endtime']) && $params['endtime'] > 0){
        	$sql .= " AND sl.timemodified < {$params['endtime']} ";	
        }
        $likesql = " AND sl.likestatus = 1 ";
		$likesdata = $this->db->get_field_sql($sql.$likesql);
        $dislikesql = " AND sl.likestatus = 2 ";
		$dislikesdata = $this->db->get_field_sql($sql.$dislikesql);
		$totallikesdata = $dislikesdata+$likesdata;
     	return compact('likesdata', 'totallikesdata', 'dislikesdata');
	}
	public function ratingsInfoReport($moduleid, $params = []){
        $ratingsql = "SELECT round(SUM(sr.rating)/count(sr.id),2) FROM {stream_rating} AS sr 
        	JOIN {course_modules} AS cm ON cm.instance = sr.itemid AND cm.module = (SELECT id FROM {modules} WHERE name LIKE 'stream') 
        	WHERE cm.id = {$moduleid} ";
    	if(isset($params['starttime']) && $params['starttime'] > 0){
        	$ratingsql .= " AND sr.timemodified > {$params['starttime']} ";	
        }
        if(isset($params['endtime']) && $params['endtime'] > 0){
        	$ratingsql .= " AND sr.timemodified < {$params['endtime']} ";	
        }
        return $this->db->get_field_sql($ratingsql);
	}
	public function activityStatus($moduleid=null, $params=null, $search=null, $timefilter=null){
		global $COURSE;
		$sql = "SELECT u.id, cm.id as cmid, concat(u.firstname,' ',u.lastname) AS fullname, 
                       u.email, sa.percentage as status, sa.timecreated, sa.timemodified, 
                       (SELECT count(attempt) FROM {stream_attempts} AS isa 
                         WHERE isa.userid = u.id AND isa.moduleid = cm.id) AS attemptval
    			 FROM {user} AS u
    			 JOIN {user_enrolments} AS ue ON ue.userid = u.id
    			 JOIN {enrol} AS e ON e.id = ue.enrolid 
    			 JOIN {course_modules} AS cm ON cm.id = {$moduleid} AND cm.course = e.courseid
    	    LEFT JOIN {stream_attempts} AS sa ON sa.userid = u.id AND sa.moduleid = cm.id 
                  AND sa.id = (SELECT MIN(id) FROM {stream_attempts} as isa 
                                WHERE isa.userid = u.id AND isa.moduleid = cm.id ) WHERE 1=1 ";
                   // AND uss.percentage = 100
		if($search != ''){
	    	$sql .= " AND CONCAT(u.firstname, u.lastname) LIKE '%". $search ."%' OR u.email LIKE '%". $search ."%' "; 

        }
        $time = explode (",", $timefilter);
        if(count($time) == 2 && $time[0]>0 && $time[1]>0) {
            $sql .= " AND sa.timemodified > {$time[0]} AND sa.timemodified < {$time[1]} ";
        }
        $sql .= " ORDER BY sa.id ASC ";
        $activitycount = count($this->db->get_records_sql($sql));
        $activities = $this->db->get_records_sql($sql, array(), $params["start"], $params["length"]);
      
	return compact('activities', 'activitycount');
	}
	public function dailyVisitsData($moduleid, $params = []){
		$sql = "SELECT id, timecreated,  count(userid) as 'visits' FROM {stream_attempts} 
			WHERE moduleid = {$moduleid} ";
		if(isset($params['starttime']) && $params['starttime'] > 0){
			$sql .= " AND timecreated > {$params['starttime']} ";
		}
		if(isset($params['endtime']) && $params['endtime'] > 0){
			$sql .= " AND timecreated < {$params['endtime']} ";
		}
		$sql .= " GROUP BY MONTH(FROM_UNIXTIME(timecreated)), DAY(FROM_UNIXTIME(timecreated)) ";
		$records = $this->db->get_records_sql($sql);
		$dailyvisits = array();
		foreach($records as $record){
			$dailyvisits[] = array('name' => date('d M Y', $record->timecreated) , 'y' => $record->visits);
		}
		return $dailyvisits;
	}
	public function totalactive($courseid, $params = []){
		$yesterdaydate =  date('d.m.Y',strtotime("-1 days"));
		$yesterday = $yesterdaydate . ' 23:59:59';
		$timestamp = strtotime($yesterday);
		$sevendaysdate =  date('d.m.Y',strtotime("-7 days"));
		$lastseven = $sevendaysdate . ' 23:59:59';
		$seventimestamp = strtotime($lastseven);
        $totalsql = "SELECT count(distinct(s.id)) FROM {stream} AS s WHERE FROM_UNIXTIME(s.timecreated) BETWEEN FROM_UNIXTIME($seventimestamp) AND FROM_UNIXTIME($timestamp)  ";
        $activesql = "SELECT count(distinct(s.id)) FROM {stream} AS s
                    JOIN {course_modules} AS cm ON cm.instance = s.id AND cm.module = (SELECT id FROM {modules} WHERE name = 'stream') WHERE deletioninprogress=0 AND FROM_UNIXTIME(s.timecreated) BETWEEN FROM_UNIXTIME($seventimestamp) AND FROM_UNIXTIME($timestamp) ";
		if(!is_null($courseid)  && $courseid > 1){
			$totalsql .= " AND s.course = {$courseid} ";
        	$activesql .= " AND cm.course = {$courseid} ";
		}
		if(isset($params['starttime']) && $params['starttime'] > 0){
			$totalsql .= " AND s.timecreated > {$params['starttime']} ";
        	$activesql .= " AND s.timecreated > {$params['starttime']} ";	
		}
		if(isset($params['endtime']) && $params['endtime'] > 0){
			$totalsql .= " AND s.timecreated < {$params['endtime']} ";
        	$activesql .= " AND s.timecreated < {$params['endtime']} ";
		}

        $trendsql = $totalsql . ' AND DATEDIFF( NOW(),FROM_UNIXTIME(s.timecreated) ) < 7 GROUP BY DAY(FROM_UNIXTIME(s.timecreated))
                                 ORDER BY timecreated ASC';
        $weeklytrends = $this->db->get_fieldset_sql($trendsql);
        
		$total = $this->db->get_field_sql($totalsql);
        
		$active = $this->db->get_field_sql($activesql);
		$total = $total ? $total : 0;
		$active = $active ? $active : 0;
    
		return compact('total', 'active');
	}
	public function totalviewssql($courseid=null, $moduleid = null, $params = []){
		$yesterdaydate =  date('d.m.Y',strtotime("-1 days"));
		$yesterday = $yesterdaydate . ' 23:59:59';
		$timestamp = strtotime($yesterday);
		$sevendaysdate =  date('d.m.Y',strtotime("-7 days"));
		$lastseven = $sevendaysdate . ' 23:59:59';
		$seventimestamp = strtotime($lastseven);
		$totalviewssql = "SELECT count(sa.id) FROM {stream_attempts} as sa JOIN {course_modules} as cm on cm.id = sa.moduleid WHERE from_unixtime(sa.timecreated) BETWEEN FROM_UNIXTIME($seventimestamp) AND FROM_UNIXTIME($timestamp) ";
		if(!is_null($courseid)  && $courseid > 1){
        	$totalviewssql .= " AND sa.courseid = {$courseid} ";
		}
		if(!is_null($moduleid) && $moduleid > 0){
			$totalviewssql .= " AND sa.moduleid = {$moduleid} ";	
		}
		if(isset($params['starttime']) && $params['starttime'] > 0){
			$totalviewssql .= " AND sa.timemodified > {$params['starttime']} ";
		}
		if(isset($params['endtime']) && $params['endtime'] > 0){
			$totalviewssql .= " AND sa.timemodified < {$params['endtime']} ";
		}
		$views = $this->db->get_field_sql($totalviewssql);
        return $views ? $views : 0;
	}
	public function videos_usersdata($courseid, $params = []){
        $completedvideosbyusersdatasql = "SELECT CONCAT(u.firstname, u.lastname) AS fullname, COUNT(DISTINCT uss.moduleid) FROM {stream_attempts} uss
        								JOIN {user} u ON u.id = uss.userid
        								WHERE u.confirmed = 1 AND uss.percentage = 100";
        // $completedusersbyvideosdatasql = "SELECT s.name, (SELECT count(DISTINCT sa.userid) AS completedusers FROM {course_modules} cm
        // 								JOIN {modules} m ON m.id = cm.module
        // 								JOIN {stream} s ON s.id = cm.instance
        // 								JOIN {stream_attempts} sa ON sa.moduleid = cm.id
        // 								WHERE sa.percentage = 100 AND m.name = 'stream') as completed,
        //                                 (SELECT count(DISTINCT sa.userid) AS completedusers FROM {course_modules} cm
        //                                 JOIN {modules} m ON m.id = cm.module
        //                                 JOIN {stream} s ON s.id = cm.instance
        //                                 JOIN {stream_attempts} sa ON sa.moduleid = cm.id
        //                                 WHERE m.name = 'stream') as attempts FROM {course_modules} cm
        //                                 JOIN {modules} m ON m.id = cm.module
        //                                 JOIN {stream} s ON s.id = cm.instance
        //                                 JOIN {stream_attempts} sa ON sa.moduleid = cm.id";
        $completedusersbyvideosdatasql = "SELECT s.name, (select count(sa.id) from {stream_attempts} sa join {course_modules} as cm on cm.id = sa.moduleid WHERE sa.courseid = cms.course and sa.moduleid = cms.id and percentage = 100) as completed, (select count(sa.id) from {stream_attempts} sa join {course_modules} as cm on cm.id = sa.moduleid WHERE sa.courseid = cms.course and sa.moduleid = cms.id) as attempts FROM {stream} as s join {course_modules} as cms on cms.course = s.course and cms.instance = s.id join {modules} as m on m.id = cms.module join {stream_attempts} as saa on saa.moduleid = cms.id WHERE m.name='stream' ";
		if(!is_null($courseid)  && $courseid > 1){
        	$completedvideosbyusersdatasql .= " AND uss.courseid = {$courseid} ";
			$completedusersbyvideosdatasql .= " AND cms.course = {$courseid} ";
		}
		if(isset($params['starttime']) && $params['starttime'] > 0){
        	$completedvideosbyusersdatasql .=" AND uss.timemodified >= {$params['starttime']} ";	
        	$completedusersbyvideosdatasql .=" AND saa.timemodified >= {$params['starttime']} ";	
        }
        if(isset($params['endtime']) && $params['endtime'] > 0){
        	$completedvideosbyusersdatasql .=" AND uss.timemodified <= {$params['endtime']} ";	
        	$completedusersbyvideosdatasql .=" AND saa.timemodified <= {$params['endtime']} ";	
        }
		$completedvideosbyusersdatasql .=  " GROUP BY uss.userid";
		$completedusersbyvideosdatasql .=  " GROUP BY s.name";

		$completedvideosbyusersdata = $this->db->get_records_sql_menu($completedvideosbyusersdatasql);
        $completedusersbyvideosdata = $this->db->get_records_sql($completedusersbyvideosdatasql);

        $completedvideosbyusers = array();
        foreach ($completedvideosbyusersdata as $k => $completedvideosbyuser) {
            $completedvideosbyusers[] = array('name' => $k , 'y' => $completedvideosbyuser);
        }

        $completedusersbyvideos = array();
        $completed = array();
        $attempts = array();
        foreach ($completedusersbyvideosdata as  $completedusersbyvideo) {
               $completedusersbyvideos['categories'][] =  $completedusersbyvideo->name;
               $completed[] =  $completedusersbyvideo->completed;
               $attempts[] =  $completedusersbyvideo->attempts;
        }
        $completedusersbyvideos['data'] = array(['type' => 'column', 'name' => 'Attempts', 'data' => array_values($attempts)],
                                                ['type' => 'spline', 'name' => 'Compelted','data'=>  array_values($completed)]);
		return compact('completedvideosbyusers', 'completedusersbyvideos');
	}
	public function like_dislike_served($courseid, $params = []){
		$yesterdaydate =  date('d.m.Y',strtotime("-1 days"));
		$yesterday = $yesterdaydate . ' 23:59:59';
		$timestamp = strtotime($yesterday);
		$sevendaysdate =  date('d.m.Y',strtotime("-7 days"));
		$lastseven = $sevendaysdate . ' 23:59:59';
		$seventimestamp = strtotime($lastseven);
        $likesdatasql = "SELECT COUNT(sl.likestatus) as dislikes FROM {stream_like} AS sl
                            JOIN {stream} AS s ON s.id = sl.itemid
                            WHERE likestatus = 1 AND FROM_UNIXTIME(sl.timecreated) BETWEEN FROM_UNIXTIME($seventimestamp) AND FROM_UNIXTIME($timestamp) ";
        $servedtimesql = "SELECT round((sum(sa.completedduration)/60),2) as servedtime
            FROM {stream} AS s
            JOIN {course_modules} AS cm ON cm.instance = s.id 
                AND cm.module = (SELECT id FROM {modules} WHERE name LIKE 'stream')
            JOIN {stream_attempts} AS sa ON sa.moduleid = cm.id 
            JOIN {course} AS c ON c.id = s.course 
            JOIN {user} AS u ON u.id = s.usercreated
            WHERE FROM_UNIXTIME(sa.timecreated) BETWEEN FROM_UNIXTIME($seventimestamp) AND FROM_UNIXTIME($timestamp) "; 
        $dislikessql = "SELECT COUNT(sl.likestatus) as dislikes FROM {stream_like} AS sl
                            JOIN {stream} AS s ON s.id = sl.itemid
                            WHERE likestatus = 2 AND FROM_UNIXTIME(sl.timecreated) BETWEEN FROM_UNIXTIME($seventimestamp) AND FROM_UNIXTIME($timestamp)  ";
		if(!is_null($courseid)  && $courseid > 1){
			$likesdatasql .= " AND s.course = {$courseid} ";
			$servedtimesql .= " AND cm.course = {$courseid} ";
        	$dislikessql .= " AND s.course = {$courseid} ";
		}
        
		if(isset($params['starttime']) && $params['starttime'] > 0){
			$likesdatasql .= " AND sl.timemodified > {$params['starttime']} ";
			$servedtimesql .= " AND sa.timecreated  > {$params['starttime']} ";
        	$dislikessql .= " AND sl.timemodified > {$params['starttime']} ";
		}
		if(isset($params['endtime']) && $params['endtime'] > 0){
			$likesdatasql .= " AND sl.timemodified < {$params['endtime']} ";
			$servedtimesql .= " AND sa.timecreated  < {$params['endtime']} ";
        	$dislikessql .= " AND sl.timemodified < {$params['endtime']} ";
		}

        $servedtrendsql = $servedtimesql . ' AND DATEDIFF( NOW(),FROM_UNIXTIME(sa.timecreated) ) < 7 GROUP BY DAY(FROM_UNIXTIME(sa.timecreated))
                                       ORDER BY sa.timecreated ASC';
        $weeklyservedtrends = $this->db->get_fieldset_sql($servedtrendsql);

        $likestrendsql = $likesdatasql . ' AND DATEDIFF( NOW(),FROM_UNIXTIME(sl.timecreated) ) < 7 GROUP BY DAY(FROM_UNIXTIME(sl.timecreated))
                                 ORDER BY sl.timecreated ASC';
        $weeklylikestrends = $this->db->get_fieldset_sql($likestrendsql);

        $disliketrendsql = $dislikessql . ' AND DATEDIFF( NOW(),FROM_UNIXTIME(sl.timecreated) ) < 7 GROUP BY DAY(FROM_UNIXTIME(sl.timecreated))
                                 ORDER BY sl.timecreated ASC';
        $weeklydisliketrends = $this->db->get_fieldset_sql($disliketrendsql);


		$totallikesdata = $this->db->get_field_sql($likesdatasql);
        $servedtime = $this->db->get_field_sql($servedtimesql);
		$totaldislikes = $this->db->get_field_sql($dislikessql);
		$totallikesdata = $totallikesdata ? $totallikesdata : 0;
        $servedtime = $servedtime ? $servedtime : 0;
		$totaldislikes = $totaldislikes ? $totaldislikes : 0;
		return compact('totallikesdata', 'servedtime', 'totaldislikes','weeklyservedtrends','weeklylikestrends','weeklydisliketrends');
	}
	public function coursestats($courseid=null, $params=null, $search=null){
        $coursevideossql = "SELECT cm.course, c.fullname, COUNT(s.name) AS totalvideos, COUNT(cm.id) AS activevideos FROM {stream} AS s
	        JOIN {course_modules} AS cm ON cm.instance = s.id AND cm.module = (SELECT id FROM {modules} WHERE name = 'stream')
	        JOIN {course} AS c ON c.id = cm.course WHERE cm.visible = '1'";
		if(!is_null($courseid) && $courseid > 1){
			$coursevideossql .= " AND cm.course = {$courseid} ";
		}
        if($search != ''){
	    	$sql .= " AND c.fullname LIKE '%". $search ."%' "; 
        }
		$coursevideossql .= " GROUP BY cm.course";
		$coursescount = count($this->db->get_records_sql($coursevideossql));
		$courses = $this->db->get_records_sql($coursevideossql, array(), $params["start"], $params["length"]);
		return compact('courses', 'coursescount'); 
	}
	public function top_like_rate_view($courseid, $params = []){
        $toplikessql = "SELECT cm.id, s.name as streamname, module_like as value FROM {stream_ratings_likes} as srl
                        JOIN {stream} as s on s.id = srl.module_id
                        JOIN {stream_like} AS sl ON sl.itemid = srl.module_id
                        JOIN {course_modules} as cm ON cm.instance = srl.module_id WHERE 1=1 ";
		$topratingsql = "SELECT cm.id, s.name as streamname, round(sum(sr.rating)/count(sr.id), 1) AS value
			FROM {stream_rating} AS sr 
			JOIN {stream} AS s ON s.id = sr.itemid 
			JOIN {course_modules} as cm ON cm.instance = sr.itemid WHERE 1=1 ";

        $topviewssql = "SELECT cm.id, s.name as streamname, count(sa.id) as value FROM {stream_attempts} sa 
                        JOIN {course_modules} as cm on cm.id = sa.moduleid
                        join {stream} as s on s.id = cm.instance WHERE 1=1 ";

		if(!is_null($courseid)  && $courseid > 1){
			$toplikessql .= " AND s.course = {$courseid} ";
			$topratingsql .= " AND s.course = {$courseid} ";
			$topviewssql .= " AND s.course = {$courseid} ";
		}
		if(isset($params['starttime']) && $params['starttime'] > 0){
			$toplikessql .= " AND sl.timemodified > {$params['starttime']} ";
			$topratingsql .= " AND sr.timemodified > {$params['starttime']} ";
			$topviewssql .= " AND sa.timemodified > {$params['starttime']} ";
		}
		if(isset($params['endtime']) && $params['endtime'] > 0){
			$toplikessql .= " AND sl.timemodified < {$params['endtime']} ";
			$topratingsql .= " AND sr.timemodified < {$params['endtime']} ";
			$topviewssql .= " AND sa.timemodified < {$params['endtime']} ";
		}
		$toplikessql .= " GROUP BY s.id
					ORDER BY count(sl.id) DESC
                        LIMIT 10";
        $topratingsql .= " GROUP BY s.id
        			ORDER BY (sum(sr.rating)/count(sr.id)) DESC
                        LIMIT 10";   
		$topviewssql .= " GROUP BY moduleid
                        ORDER BY sum(attempt) DESC
                        LIMIT 10";
        $toplikedvideos = $this->db->get_records_sql($toplikessql);
        $topratingvideos = $this->db->get_records_sql($topratingsql);
        $topviewsvideos = $this->db->get_records_sql($topviewssql);

        $toplikes = array();
        $topratings = array();
        $topviews = array();
        $visits = array();
		$toplikesvisits = [];
        foreach ($toplikedvideos as $like) {
               $toplikes['categories'][] =  $like->streamname;
               $toplikesvisits[] = $like->value; 
        }
        $toplikes['data'] = array(['type' => 'spline', 'name' => 'Likes','data'=>  array_values($toplikesvisits), 'dataLabels'=> ['enabled' =>true]]);
        $topratingsvisits = [];
		foreach ($topratingvideos as $like) {
               $topratings['categories'][] =  $like->streamname;
               $topratingsvisits[] = $like->value; 
        }
        $topratings['data'] = array(['type' => 'spline', 'name' => 'Ratings','data'=>  array_values($topratingsvisits), 'dataLabels'=> ['enabled' =>true]]);
        $topviewsvisits = [];
		foreach ($topviewsvideos as $like) {
               $topviews['categories'][] =  $like->streamname;
               $topviewsvisits[] = $like->value; 
        }
        $topviews['data'] = array(['type' => 'spline', 'name' => 'Views','data'=>  array_values($topviewsvisits), 'dataLabels'=> ['enabled' =>true]]);

        return compact('toplikes', 'topratings', 'topviews');
	}
	public function get_week_statistics($courseid, $activityid){
		$yesterdaydate =  date('d.m.Y',strtotime("-1 days"));
		$yesterday = $yesterdaydate . ' 23:59:59';
		$timestamp = strtotime($yesterday);
		$activeVideosSql = "SELECT DAY(FROM_UNIXTIME(s.timecreated)), count(s.id) 
			FROM {stream} AS s 
			JOIN {course_modules} AS cm ON cm.course = s.course AND cm.instance = s.id 
			WHERE cm.visible = 1 AND DATEDIFF( FROM_UNIXTIME($timestamp),FROM_UNIXTIME(s.timecreated) ) < 7 
			GROUP BY DAY(FROM_UNIXTIME(s.timecreated)) ";

		$totalViewsSql = "SELECT DAY(FROM_UNIXTIME(sa.timecreated)), count(sa.id)
				FROM {stream_attempts} as sa
				JOIN {course_modules} as cm on cm.id = sa.moduleid
				WHERE DATEDIFF( FROM_UNIXTIME($timestamp),FROM_UNIXTIME(sa.timecreated) ) < 7
				";

		$totalMinutesSql = "SELECT DAY(FROM_UNIXTIME(sa.timecreated)), round((sum(sa.completedduration)/60),2) as servedtime
			FROM {stream_attempts} AS sa
            WHERE DATEDIFF( FROM_UNIXTIME($timestamp),FROM_UNIXTIME(sa.timecreated) ) < 7
            ";

        $totalLikesSql = "SELECT DAY(FROM_UNIXTIME(sl.timecreated)), count(sl.id) as likes
				FROM {stream} as s 
				join {stream_like} as sl on sl.itemid = s.id
				JOIN {course_modules} as cm on cm.instance = s.id AND cm.module = (SELECT id FROM {modules} WHERE name LIKE 'stream')
				WHERE DATEDIFF( FROM_UNIXTIME($timestamp),FROM_UNIXTIME(sl.timecreated) ) < 7 AND sl.likestatus = 1
				";

		$totalRatingsSql = "SELECT DAY(FROM_UNIXTIME(sr.timecreated)) as day,sum(rating) as rating
				FROM {stream} as s
				JOIN {stream_rating} as sr on sr.itemid = s.id
				JOIN {course_modules} as cm on cm.instance = sr.itemid and cm.module = (SELECT id FROM {modules} WHERE name LIKE 'stream')
				WHERE DATEDIFF( FROM_UNIXTIME($timestamp),FROM_UNIXTIME(sr.timecreated) ) < 7";

		$viewParams = $minuteParam = $likeParams = $ratingParam = [];
		if($activityid > 0){
			$totalViewsSql .= " AND sa.moduleid = :moduleid ";
			$viewParams['moduleid'] = $activityid;
			$totalMinutesSql .= " AND sa.moduleid = :moduleid ";
			$minuteParam['moduleid'] = $activityid;
			$totalLikesSql .= " AND cm.id = :moduleid ";
			$likeParams['moduleid'] = $activityid;
			$totalRatingsSql .= " AND cm.id = :moduleid ";
			$ratingParam['moduleid'] = $activityid;
		}else if($courseid > 0){
			$totalViewsSql .= " AND sa.courseid = :courseid ";
			$viewParams['courseid'] = $courseid;
			$totalMinutesSql .= "  AND sa.courseid = :courseid ";
			$minuteParam['courseid'] = $courseid;
			$totalLikesSql .= " AND cm.course = :courseid ";
			$likeParams['courseid'] = $courseid;
			$totalRatingsSql .= " AND cm.course = :courseid ";
			$ratingParam['courseid'] = $courseid;
		}
		$totalViewsSql .= " GROUP BY DAY(FROM_UNIXTIME(sa.timecreated)) ";
		$totalMinutesSql .= " GROUP BY DAY(FROM_UNIXTIME(sa.timecreated)) ";
		$totalLikesSql .= " GROUP BY DAY(FROM_UNIXTIME(sl.timecreated)) ";
        $totalRatingsSql .= " GROUP BY DAY(FROM_UNIXTIME(sr.timecreated)) "; 

		$activeVideos = $this->db->get_records_sql_menu($activeVideosSql);
		$totalViews = $this->db->get_records_sql_menu($totalViewsSql, $viewParams);
		$totalMinutes = $this->db->get_records_sql_menu($totalMinutesSql, $minuteParam);
		$totalLikes = $this->db->get_records_sql_menu($totalLikesSql, $likeParams);
		$totalRatings = $this->db->get_records_sql_menu($totalRatingsSql, $ratingParam);
		$likesdata = $viewsdata = $minutesdata = $videosdata = $ratingsdata = $datesdata = [];
		$day = date('d',strtotime("-1 days"));
		$index = $day;
        $m= date("m"); 
        $de= date("d"); 
        $y= date("Y"); 
		for($i=0; $i<7; $i++){
			$likesdata[$index] = isset($totalLikes[$index]) ? $totalLikes[$index] : 0;
			$viewsdata[$index] = isset($totalViews[$index]) ? $totalViews[$index] : 0;
			$minutesdata[$index] = isset($totalMinutes[$index]) ? $totalMinutes[$index] : 0;
			$videosdata[$index] = isset($activeVideos[$index]) ? $activeVideos[$index] : 0;
			$ratingsdata[$index] = isset($totalRatings[$index]) ? $totalRatings[$index] : 0;
			$datesdata[] = date('dS', mktime(0,0,0,$m,($de-($i+1)),$y)); 
			$index--;
			if($index == 0){
				$index = date('t', strtotime('-1 months'));
			}
		}

		$weekvideos['data'] = array(['type' => 'spline', 'name' => 'Videos','data'=> array_values(array_reverse($videosdata)), 'datesinfo' => array_values(array_reverse($datesdata))]);
		$weekminutes['data'] = array(['type' => 'spline', 'name' => 'Minutes','data'=> array_values(array_reverse($minutesdata)), 'datesinfo' => array_values(array_reverse($datesdata))]);
		$weekviews['data'] = array(['type' => 'spline', 'name' => 'Views','data'=> array_values(array_reverse($viewsdata)), 'datesinfo' => array_values(array_reverse($datesdata))]);
		$weeklikesdislikes['data'] = array(['type' => 'spline', 'name' => 'Likes','data'=> array_values(array_reverse($likesdata)), 'datesinfo' => array_values(array_reverse($datesdata))]);
		$weekratings['data'] = array(['type' => 'spline', 'name' => 'Ratings','data'=> array_values(array_reverse($ratingsdata)), 'datesinfo' => array_values(array_reverse($datesdata))]);
		return compact('weekvideos', 'weekminutes', 'weekviews', 'weeklikesdislikes', 'weekratings');
	}
	public function timeperiod($courseid, $timeperiod='DAY'){
        $timeperiodssql = "SELECT CONCAT(u.firstname, ' ',u.lastname) as fullname, count(DISTINCT moduleid) AS completedvideos FROM {stream_attempts} as sa
        				JOIN {user} as u ON u.id = sa.userid
        				WHERE FROM_UNIXTIME(sa.timecreated) >= DATE_SUB(CURDATE(), INTERVAL 1 {$timeperiod}) AND percentage = 100";

		if(!is_null($courseid)  && $courseid > 1){
			$timeperiodssql .= " AND sa.courseid = {$courseid} ";
		}
		$timeperiodssql .= " GROUP BY userid";
        return $this->db->get_records_sql($timeperiodssql);
	}
	public function likes_based_duration($courseid, $params = []){
		global $DB;
		$fiveminssql = "SELECT cm.id, s.name, module_like as likes FROM {stream_ratings_likes} as srl
                        JOIN {stream} as s on s.id = srl.module_id
                        JOIN {stream_like} AS sl ON sl.itemid = srl.module_id
                        JOIN {course_modules} as cm ON cm.instance = srl.module_id
                        WHERE sl.itemid in (select distinct cm.instance FROM {stream_attempts} as sa
                         						JOIN {course_modules} as cm on cm.id = sa.moduleid
                                                WHERE duration < 300) ";

        $fivetotenminssql = "SELECT cm.id, s.name, module_like as likes FROM {stream_ratings_likes} as srl
	                        JOIN {stream} as s on s.id = srl.module_id
    	                    JOIN {stream_like} AS sl ON sl.itemid = srl.module_id
        	                JOIN {course_modules} as cm ON cm.instance = srl.module_id
            	            WHERE sl.itemid in (select distinct sa.moduleid FROM {stream_attempts} as sa
                            						JOIN {course_modules} as cm on cm.id = sa.moduleid
                                                    WHERE duration BETWEEN 301 AND 600) ";
        $abovetenminssql = "SELECT cm.id, s.name, module_like as likes FROM {stream_ratings_likes} as srl
                        	JOIN {stream} as s on s.id = srl.module_id
                        	JOIN {stream_like} AS sl ON sl.itemid = srl.module_id
                        	JOIN {course_modules} as cm ON cm.instance = srl.module_id
                       		WHERE sl.itemid in (select distinct sa.moduleid FROM {stream_attempts} as sa 
                                                	JOIN {course_modules} as cm on cm.id = sa.moduleid
                                                    WHERE duration > 601) ";

		if(!is_null($courseid)  && $courseid > 1){
			$fiveminssql .= " AND s.course = {$courseid} ";
			$fivetotenminssql .= " AND s.course = {$courseid} ";
			$abovetenminssql .= " AND s.course = {$courseid} ";
		}
		if(isset($params['starttime']) && $params['starttime'] > 0){
			$fiveminssql .= " AND sl.timemodified > {$params['starttime']} ";
			$fivetotenminssql .= " AND sl.timemodified > {$params['starttime']} ";
			$abovetenminssql .= " AND sl.timemodified > {$params['starttime']} ";
		}
		if(isset($params['endtime']) && $params['endtime'] > 0){
			$fiveminssql .= " AND sl.timemodified < {$params['endtime']} ";
			$fivetotenminssql .= " AND sl.timemodified < {$params['endtime']} ";
			$abovetenminssql .= " AND sl.timemodified < {$params['endtime']} ";
		}
		$fiveminssql .= " GROUP BY s.id ";
		$fivetotenminssql .= " GROUP BY s.id ";
		$abovetenminssql .= " GROUP BY s.id ";
		$fivemins = array_values($this->db->get_records_sql($fiveminssql));
		$fivetotenmins = array_values($this->db->get_records_sql($fivetotenminssql));
		$abovetenmins = array_values($this->db->get_records_sql($abovetenminssql));
        return compact('fivemins', 'fivetotenmins', 'abovetenmins'); 
	}
	public function reportsHeader($courseid, $params = []){
		$reports = [];
		extract($this->totalactive($courseid, $params));
		$reports['active'] = $active;
		$reports['total'] = $total;
		extract($this->like_dislike_served($courseid, $params));
		$reports['servedtime'] = $servedtime;
		$reports['totalviews'] = $this->totalviewssql($courseid, null, $params);
		$reports['totallikesdata'] = $totallikesdata;
		$reports['totaldislikes'] = $totaldislikes;
		return $reports;
	}
	public function reportHeader($moduleid, $params= []){
		$report = [];
		$likesInfoReport = $this->likesInfoReport(null, $moduleid, $params);
		extract($likesInfoReport);
		$report['totalviews'] = $this->totalviewssql(null, $moduleid, $params);
		$report['likesinfo'] = $likesdata;
		$report['dislikesinfo'] = $dislikesdata;
		$rating = $this->ratingsInfoReport($moduleid, $params);
		if($rating == ''){
			$report['ratingsinfo'] = '0';	
		} else {
		    $report['ratingsinfo'] = $rating;			
		}
		return $report;
	}
}
