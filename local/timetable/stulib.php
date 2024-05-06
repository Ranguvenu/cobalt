<?php

// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

/**
 * List the tool provided in a course
 *
 * @package    manage_departments
 * @subpackage  list of all functions which is used in departments plugin
 * @copyright  2012 Hemalatha arun <Hemaltha@eabyas.in>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
require_once($CFG->dirroot . '/local/lib.php');
require_once($CFG->dirroot . '/message/lib.php');
require_once($CFG->dirroot . '/local/timetable/lib.php');
$hier = new hierarchy();

function local_timetable_timetablelayout_studentdisplay($timelayoutid, $enrolledcllist) {
    global $DB, $USER, $PAGE, $OUTPUT;
     try{
// ------- getting timeintervals-----------------------------------
    $timetablelayoutinfo = $DB->get_record('local_batch_timetablelayout', array('id' => $timelayoutid, 'visible' => 1));
    
    $timeintervalslist = $DB->get_records_sql("select ts.id as slotid ,ti.id,ti.semesterid, ts.starttime,ts.endtime  from {local_timeintervals_slots} as ts
                                                     join {local_timeintervals} as ti on ti.id=ts.timeintervalid
             where ts.visible=1 and  
             ts.timeintervalid={$timetablelayoutinfo->timeintervalid} order by ts.starttime ASC ");
    
    if (empty($timeintervalslist)) {
        throw new Exception('set the timeintervals to proceed further');
    }

//----- adding timetable layout info------------------
    foreach ($timeintervalslist as $timeinterval) {

        $timeinterval->timelayout = $timetablelayoutinfo;
        $timelayout_intervalinfo[] = $timeinterval;
    }

    $days = array('M' => 'mon', 'TU' => 'tue', 'W' => 'wed', 'TH' => 'thur', 'F' => 'fri', 'SA' => 'sat','SU'=>'sun');

//---------creating two dimensional array ---to  make available info for each and every cell
    foreach ($days as $key => $value) {
        $daytimeslots[$key] = $timelayout_intervalinfo;
    }// end of foreach

    $table = new html_table();
    $table->head[] = "   ";
    foreach ($timeintervalslist as $timeinterval) {
        $table->head[] = date("g:i", strtotime($timeinterval->starttime)) . '-' . date("g:i", strtotime($timeinterval->endtime));
    }

//------------arranging table data------------------------
    foreach ($daytimeslots as $day => $dayslot) {
        $dayname = $day;
        $cells = array();
        $colsspan_array = array();
        $cellcolspan = array();
        $cells[] = local_timetable_date_format($dayname);
        $i = 0;
        foreach ($dayslot as $slots) {
            // print_object($slots);
            $timel = $slots->timelayout;            
            $cellcontent = local_timetable_layoutcell_query($slots, $dayname, $enrolledcllist);
              if(isset($cellcontent->set))
            $set=$cellcontent->set;
            else
            $set=array();
        
            $cellcolspans[$slots->slotid] = array('set'=>$set,'colspan'=>$cellcontent->colspan);
            
            // print_object($cellcolspans);
             $colspans_array = array_values($cellcolspans);   
            
             $optioncell = new html_table_cell($cellcontent->content);
            //print_object($colspans_array);
            
              //$colsspans_array = array_values($cellcolspan);
             if ($colspans_array[$i] > 1) {
                  //--------comparing(previous and present cell) each and every cell colspan--------------------------    
                if(  isset($colspans_array[$i]['set']) &&  isset($colspans_array[$i-1]['set']) && $colspans_array[$i-1]['set'] && !empty($colspans_array[$i]['set']) ){

                 $setdiff=array_diff($colspans_array[$i]['set'],$colspans_array[$i-1]['set']);
                 $setdiffcount=sizeof($setdiff);
                // $cond=($setdiffcount<=0) ;
                 }
                 else{
                  // ----- if setdiffcount is 1 theres no colspan  and it is single cell  
                 $setdiffcount=1;
                 //$cond=1;
                 }


                 
                if ($i > 0 && $colspans_array[$i - 1]['colspan'] > 1 && ($setdiffcount<=0) && $colspans_array[$i]['colspan'] > 1 )
                //-----if colspan is > 1 then that cell content will be assigned to empty
                    $optioncell = '';                
                else
                    $optioncell->colspan = $colspans_array[$i]['colspan'];                
                }        
            
            
            
            
            //
            //$cellcontent = local_timetable_layoutcell_query($slots, $dayname, $enrolledcllist);
            //$cellcolspan[$slots->slotid] = $cellcontent->colspan;
            //
            //$optioncell = new html_table_cell($cellcontent->content);
            //// $colsspan_array =  $cellcolspan
            ////--------comparing each and every cell colspan
            //$colsspan_array = array_values($cellcolspan);
            //if ($colsspan_array[$i] > 1) {
            //    if ($i > 0 && $colsspan_array[$i - 1] > 1)
            //        $optioncell = '';
            //    else
            //        $optioncell->colspan = $colsspan_array[$i];
            //}
            //
            
            if (!empty($optioncell)) {
                $cells[] = $optioncell;
            }

            $i++;
        } // end of inner foreach

        $data1[] = $cells;
    } // end of outer foreach
    // $data1=$data1[0];
    $table->id = "local_timetable_studentlayout";
    $table->size = array('', '10%', '10%', '10%', '10%', '10%', '10%', '10%', '10%', '10%');
    $table->align = array('left', 'left', 'left', 'left', 'left', 'left', 'left', 'left', 'left');
    $table->width = '100%';
    $table->data = $data1;

    $response = html_writer::table($table);
    
     }
     catch (Exception $e) {
        $error_cl = new stdclass();
        $msg = $e->getMessage();
        $error_cl->msg = $msg;

    }
    if (isset($error_cl->msg)) {
        return $error_cl;
    } else {
        return $response;
    }

    
}

function local_timetable_getstudent_detail() {
    global $DB, $USER, $PAGE, $OUTPUT;
    $flag = 0;
    try {
        if (!isloggedin()){
           // throw new exception('login requird');
        }

        $userinfo = new stdclass();
//----------- get_student_information--------------
        $userdata = $DB->get_record('local_userdata', array('userid' => $USER->id));
        if (empty($userdata)){
            return 0;
            die;
        }
        
        
        
        $userinfo->userid = $USER->id;
        $userinfo->batchid = $userdata->batchid;
        $userinfo->schoolid = $userdata->schoolid;
        $userinfo->programid = $userdata->programid;
       
       //---------fetching active semester and offering period---------------- 
        $today=date('Y-m-d');  
        $activeplan_sql ="select active.id,active.planid,active.semesterid from {local_activeplan_batch} as active
               Join {local_semester} as sem on sem.id=active.semesterid and
               active.batchid ={$userinfo->batchid}";
               
         $dateconditon =" AND '{$today}' between DATE(FROM_UNIXTIME(sem.startdate)) and  DATE(FROM_UNIXTIME(sem.enddate))";
        
        $activeplaninfo =$DB->get_record_sql( $activeplan_sql.$dateconditon);
        
        //--------if current semester is not available, go with the fetching previous semester---------------
        if(empty($activeplaninfo)){          
             $dateconditon =" AND '{$today}' >=DATE(FROM_UNIXTIME(sem.enddate)) order by sem.enddate desc limit 1";            
             $activeplaninfo =$DB->get_record_sql( $activeplan_sql.$dateconditon);
        }
        
        
        if (empty($activeplaninfo)){
          //  throw new exception("presently no current semester offering period available");
          $semofferingperiod=0;          
          return $userinfo;
          die;
        }
        else
         $semofferingperiod=$activeplaninfo->semesterid;

        $userinfo->semesterid = $semofferingperiod;
        $userinfo->planid = $activeplaninfo->planid;    

  
        //----get student enrolled classes of active plan
       $userclasssql = "select id,classid,semesterid,planid,batchid from {local_user_clclasses} where
       userid={$USER->id} and semesterid ={$userinfo->semesterid}
       and planid={$userinfo->planid} and batchid={$userinfo->batchid}
       and studentapproval=1 and registrarapproval=1 order by classid ASC";
        $enrolleduserclasses = $DB->get_records_sql($userclasssql);        
        
         //print_object($enrolleduserclasses);
        if(empty($enrolleduserclasses)){       
           $response=local_timetable_get_previoussemester_clclasses($userinfo);
           if(empty($response)){
               return $userinfo;
               die;
           }
           else{
              $enrolleduserclasses = $response['enrolleduserclasses'];
              $userinfo = $response['userinfo'];           
           }
        }
        
        
        

        foreach ($enrolleduserclasses as $key => $value) {
            $userclasseslist[] = $value->classid;
        }
         if($userclasseslist)
        $userinfo->classes = $userclasseslist;
          
        //------------ get timelayout info-----------------
        //$activetimelayout = $DB->get_record('local_batch_timetablelayout', array('schoolid' => $userinfo->schoolid,
        //    'programid' => $userinfo->programid,
        //    'batchid' => $userinfo->batchid,
        //    'semesterid' => $userinfo->semesterid,
        //    'planid' => $userinfo->planid,'visible'=>1));

        $activetimelayout_id=local_timetable_gettimetable_layoutid_forstudent($userinfo->schoolid, $userinfo->programid, $userinfo->batchid, $userinfo->semesterid,$userinfo->planid  );    
       
        if (empty($activetimelayout_id)){            
             return $userinfo;
            die;
        }
        
        $userinfo->timelayoutid = $activetimelayout_id;

    } catch (Exception $e) {
        $error_cl = new stdclass();
        $msg = $e->getMessage();
        $error_cl->msg = $msg;

    }
    if (isset($error_cl->msg)) {
        return $error_cl;
    } else {
        return $userinfo;
    }
}

/* to get previous semester classes only when current semester classes are not available
 * @param : $userinfo  object  having studentinfo like batch program etc
 * return : object enrolled classes list and modified userinfo 
 **/
function local_timetable_get_previoussemester_clclasses($userinfo){
    global $DB, $USER; $response =array();
    $today=date('Y-m-d');          
    $activeplan_sql ="select active.id,active.planid,active.semesterid from {local_activeplan_batch} as active
        Join {local_semester} as sem on sem.id=active.semesterid and
        active.batchid ={$userinfo->batchid}    
        AND '{$today}' >=DATE(FROM_UNIXTIME(sem.enddate)) order by sem.enddate desc limit 1";            
    $activeplaninfo =$DB->get_record_sql( $activeplan_sql.$dateconditon);  
    if( $activeplaninfo) {    
        $userinfo->semesterid = $activeplaninfo->semesterid;
        $userinfo->planid = $activeplaninfo->planid;     
        //----get student enrolled classes of active plan
        $userclasssql = "select id,semesterid,classid,planid,batchid from {local_user_clclasses} where
        userid={$USER->id} and semesterid ={$userinfo->semesterid}
        and planid={$userinfo->planid} and batchid={$userinfo->batchid}
        and studentapproval=1 and registrarapproval=1 order by classid ASC";
        $enrolleduserclasses = $DB->get_records_sql($userclasssql);     
        //   print_object($enrolleduserclasses);
        $response['userinfo']= $userinfo;
        $response['enrolleduserclasses'] =$enrolleduserclasses;
        
        return $response;
       
    }
    else{
        return 0;
    }
    
} // end of function

function   local_timetable_gettimetable_layoutid_forstudent($schoolid, $programid, $batchid, $semesterid, $planid){
    global $DB, $CFG, $USER;
    $today= date('Y-m-d');
     $mainsql= "select id,schoolid,programid,batchid,semesterid,planid from {local_batch_timetablelayout}
             where schoolid = {$schoolid} and 
            programid = {$programid} and
            batchid = {$batchid} and
            semesterid = {$semesterid} and
            planid = {$planid} and visible=1";            
            
    $datecond = " AND startdate and enddate is not null AND '{$today}' between DATE(FROM_UNIXTIME(startdate)) and DATE(FROM_UNIXTIME(enddate)) ";    
    $withdate_existrecords=$DB->get_record_sql($mainsql.$datecond);
  //   print_object($withdate_existrecords);
    if($withdate_existrecords){
        return ($withdate_existrecords->id);
    }
    else{        

        $record=$DB->get_record_sql($mainsql." AND (startdate and enddate) is NULL");
        
        if($record)
        return $record->id;
        else
        return false; 
    }  
    
} // end of function


/* To do: sorting classlist in ascending order
 * @param: $data object datatable
 * @return object 
 **/
function local_timetable_sort_classlist($data){
  global $CFG, $USER, $DB;
    $length= sizeof($data);
        foreach($data as $key=>$value){
          
           for($i=0; $i<($length-($key+1)); $i++){                
               if($data[$i][0] > $data[$i+1][0]){
                 $temp=$data[$i];
                 $data[$i]=$data[$i+1];
                 $data[$i+1]=$temp;                 
               }            
           } // inner loop
        } // outer loop
        
        foreach($data as $key => $innerarray){
            unset($innerarray[0]);
            $data[$key]= $innerarray;            
        }
    return $data;    
    
} //end of function
function local_timetable_activesemester_student_detail($userid = '') {
    global $DB, $USER, $PAGE, $OUTPUT;
    $flag = 0;
    if(!$userid){
        $userid = $USER->id;
    }
    try {
        if (!isloggedin()){
           // throw new exception('login requird');
        }

        $userinfo = new stdclass();
//----------- get_student_information--------------
        $userdata = $DB->get_record('local_userdata', array('userid' => $userid));
        if (empty($userdata)){
            return 0;
            die;
        }
        
        
        
        $userinfo->userid = $userid;
        $userinfo->batchid = $userdata->batchid;
        $userinfo->schoolid = $userdata->schoolid;
        $userinfo->programid = $userdata->programid;
       
       //---------fetching active semester and offering period---------------- 
        $today=date('Y-m-d');  
        $activeplan_sql ="select active.id,active.planid,active.semesterid from {local_activeplan_batch} as active
               Join {local_semester} as sem on sem.id=active.semesterid and
               active.batchid ={$userinfo->batchid}";
               
         $dateconditon =" AND '{$today}' between DATE(FROM_UNIXTIME(sem.startdate)) and  DATE(FROM_UNIXTIME(sem.enddate))";
        
        $activeplaninfo =$DB->get_record_sql( $activeplan_sql.$dateconditon);
        
        //--------if current semester is not available, go with the fetching previous semester---------------
        if(empty($activeplaninfo)){          
             $dateconditon =" AND '{$today}' >=DATE(FROM_UNIXTIME(sem.enddate)) order by sem.enddate desc limit 1";            
             $activeplaninfo =$DB->get_record_sql( $activeplan_sql.$dateconditon);
        }
        
        
        if (empty($activeplaninfo)){
          //  throw new exception("presently no current semester offering period available");
          $semofferingperiod=0;          
          return $userinfo;
          die;
        }
        else
         $semofferingperiod=$activeplaninfo->semesterid;

        $userinfo->semesterid = $semofferingperiod;
        $userinfo->planid = $activeplaninfo->planid;    

  
        //----get student enrolled classes of active plan
       $userclasssql = "select id,classid,semesterid,programid,planid,batchid from {local_user_clclasses} where
       userid={$userid} and semesterid ={$userinfo->semesterid}
       and planid={$userinfo->planid} and batchid={$userinfo->batchid}
       and studentapproval=1 and registrarapproval=1 order by classid ASC";
        $enrolleduserclasses = $DB->get_records_sql($userclasssql);        
        
        //////// //print_object($enrolleduserclasses);
        ////////if(empty($enrolleduserclasses)){       
        ////////   $response=local_timetable_get_previoussemester_clclasses($userinfo);
        ////////   if(empty($response)){
        ////////       return $userinfo;
        ////////       die;
        ////////   }
        ////////   else{
        ////////      $enrolleduserclasses = $response['enrolleduserclasses'];
        ////////      $userinfo = $response['userinfo'];           
        ////////   }
        ////////}
        
        
        

        foreach ($enrolleduserclasses as $key => $value) {
            $userclasseslist[] = $value->classid;
        }
         if($userclasseslist)
        $userinfo->classes = $userclasseslist;
          
        //------------ get timelayout info-----------------
        //$activetimelayout = $DB->get_record('local_batch_timetablelayout', array('schoolid' => $userinfo->schoolid,
        //    'programid' => $userinfo->programid,
        //    'batchid' => $userinfo->batchid,
        //    'semesterid' => $userinfo->semesterid,
        //    'planid' => $userinfo->planid,'visible'=>1));

        $activetimelayout_id=local_timetable_gettimetable_layoutid_forstudent($userinfo->schoolid, $userinfo->programid, $userinfo->batchid, $userinfo->semesterid,$userinfo->planid  );    
       
        if (empty($activetimelayout_id)){            
             return $userinfo;
            die;
        }
        
        $userinfo->timelayoutid = $activetimelayout_id;

    } catch (Exception $e) {
        $error_cl = new stdclass();
        $msg = $e->getMessage();
        $error_cl->msg = $msg;

    }
    if (isset($error_cl->msg)) {
        return $error_cl;
    } else {
        return $userinfo;
    }
}


?>