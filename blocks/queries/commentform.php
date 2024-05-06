<?php

function commenthtmlform($loginuserid, $studentid = null) {
    global $CFG, $DB;
    $popup = '';
    
    $popup .= "<div id='basicModal$loginuserid' style='display:none;'><div class='error_box$loginuserid'></div>";
    if (is_null($studentid))
        $actionpage = $CFG->wwwroot.'/blocks/queries/comment_emailtostudent.php';
    else
        $actionpage = $CFG->wwwroot.'/blocks/queries/comment_emailtostudent.php?studentid='.$studentid.'';
        $popup .= '<form name="myForm'.$loginuserid.'" method="post" action="'.$actionpage.'" onsubmit="return formvalidate('.$loginuserid.')">';
        $popup .= "<div>";
        $result = $DB->get_record('block_queries', array('id' => $loginuserid));
        $postedby = $result->postedby;

    // Asking question to the user.
    $risedq = $result->userid;
    $querieraisduser = $DB->get_record_sql("SELECT * FROM {user} WHERE id = $risedq");
    $postedusername = fullname($querieraisduser);
    // Asking question to the user.
    // query posted user.
    $posteduser = $DB->get_record_sql("SELECT * FROM {user} WHERE id = $postedby");
    $posteduserofperson = fullname($posteduser);
    $userposted = $posteduserofperson ? $posteduserofperson : 'N/A';

    /*** Manager can view post to who send to whom***/
    if (has_capability('block/queries:manager', context_system::instance())) {
        $firstname = "
        <div class='col-6 mb-3'>
            <span class='queries_postedusername '>
            <span class='posted text-muted'>".get_string('postedby', 'block_queries')."</span>
            <span class='postercolon '>:</span>
            </span>
            <span class='usep_value'>".$userposted.'</span>
        </div>'.
        "<div class='col-6 mb-3'>
            <span class='queries_postedusername '>
            <span class='posted text-muted'>".get_string('askingquerieto', 'block_queries')."</span>
            <span class='postercolon '>:</span>
            </span>
            <span class='usep_value'>".$postedusername.'</span>
        </div>';
        /*** Manager can view post to who send to whom***/
    } else {
        $firstname = "
        <div class='col-6 mb-3'>
        <span class='queries_postedusername '>
        <span class='posted text-muted'>".get_string('postedby', 'block_queries')."</span>
        <span class='postercolon '>:</span>
        </span>
        <span class='usep_value'>".$userposted.'</span>
        </div>';
    }
    // End here.
    $postedtime = "<span class='queries_postedtime'>".date("d/m/y h:i a", $result->timecreated)."</span>";
    $popup .= "<div class='queries_querydetailsforcomment'><div class='queries_querydata'><div class='row'>";
    $popup .= $firstname."
                <div>
                    <span class='queries_postedusername '>
                                       </span>
                    <span class='usep_value'>".''.'</span>
                </div>';
    $popup .= "<div class='col-6 mb-3'>
                    <span class='queries_postedusername '>
                    <span class='posted text-muted'>".get_string('subjectt', 'block_queries')."</span>
                    <span class='postercolon '>:</span>
                    </span>
                    <span class='usep_value'>".$result->subject.'</span>
                </div>';
    $popup .= "<div class='col-6 mb-3'>
                <span class='queries_postedusername '>
                <span class='posted text-muted'>".get_string('descriptionn', 'block_queries')."</span>
                <span class='postercolon '>:</span>
                </span>
                <span class='usep_value'>".$result->description.'</span>
            </div>';
    $popup .= "</div></div></div>";
    $popup .= "<div class='queries_commentformfields ml-0'>";
    $popup .= "<input type='hidden' name='queryid' value='$loginuserid'>";
    $sesskey = sesskey();
    $popup .= "<input type='hidden' name='sesskey' value='$sesskey'>";

    $popup .= "<div>";
    $popup .= "<div class='d-flex query_label_area'>";
    $popup .= "<label for='comments$loginuserid' class='queries_summarylabel text-muted'>".get_string('reply', 'block_queries')."<span style='color:red;'>*</span></label>";
    $popup .= "<textarea class='form-control postcomment' name='comment' id='comments$loginuserid' rows='3' cols='30'></textarea>";
    $popup .= "</div>";
    $popup .= "<div id='queries_submitbutton' class='sbmt_btn text-center ml-0 mt-4'>";
    $popup .= "<input class='btn btn-primary' type='submit' name='submit' id='submit$loginuserid' value='Submit'>";
    $popup .= "</div>";
    $popup .= "</div>";
    $popup .= "</form>";
    $popup .= "</div>";
    return $popup;
}
