

    var checkLength=function(formvalue, name) {
        if (formvalue == 0) {
            alert("select the " + name + " to proceed");      
            return false;
        } else {            
            return true;
        }
    } // end of function
    
  
    
    function ajax_validation(res,submittedvalue,form1, from){
        if (res) {            
        var submitted_array = $.parseJSON(submittedvalue);
        $.each(submitted_array, function (i, item) {
         if (item.name == 'scheduleid') {
            scheduleid = item.value;
         }
        });                 
            
        var url = M.cfg.wwwroot + '/local/timetable/ajaxvalidation.php';
        if (from=='submitteddata') {
                var request = $.ajax({
                url: url,
                method: "POST",
                data: {submittedata: submittedvalue}
    
            });
        }
        else{     
                var request = $.ajax({
                url: url,
                method: "POST",
                data: {customeformdata: submittedvalue}
    
            });
            
        }     

        request.done(function (results) {
            
          var posts = JSON.parse(results);
          var response = posts.value;
           
            if (response>0) {  
              local_timetable_make_ajaxcall(submittedvalue,form1);
              if (scheduleid) 
                  location.reload();              
              }
            else {
                alert(posts.comment);
                return false;
            }  
        });

        request.fail(function (jqXHR, textStatus) {
            alert("Request failed: " + textStatus);

        });
        
      } // end  of if condition  
    
    } // end of function




function custom_form_validation(submittedvalue,form1) {
     form1 = form1 || null;
    var valid, classid, classtype;
    var submitted_array = $.parseJSON(submittedvalue);
    $.each(submitted_array, function (i, item) {

        if (item.name == 'customtext') {
            customtext = item.value;
        }
        if (item.name == 'timeintervals_dialog') {
            timeintervals = item.value;
        }

    });
    var customtext_response = checkLength(customtext, "Customtext");
    var timeinterval_response = checkLength(customtext, "Timeintervals");

    if (customtext_response && timeinterval_response) {
        
        ajax_validation(true,submittedvalue, form1,"customeformdata");
    }  

} // end of function



 function form_validation(submittedvalue, form1){

     form1 = form1 || null;
    var valid, classid, classtype;
    var submitted_array = $.parseJSON(submittedvalue);

    $.each(submitted_array, function (i, item) {

        if (item.name == 'classid') {
            classid = item.value;
        }
        if (item.name == 'classtype') {
            classtype = item.value;
        }

    });
    var classresponse = checkLength(classid, "class");
    var classtyperesponse = checkLength(classtype, "classtype");
    if (classresponse && classtyperesponse) {
    
        ajax_validation(true,submittedvalue, form1,"submitteddata");
    }  

   } // end of funtion



function local_timetable_make_ajaxcall(str,form1) {

    // alert('inside the ajaX call');
     form1 = form1 || null;
    var url = M.cfg.wwwroot + '/local/timetable/timelayout_ajax.php';
    var request = $.ajax({
        url: url,
        method: "POST",
        data: {serialvalue: str}

    });

    request.done(function (response) {
       // alert('inside the ajax response');

        if (response == 1) {
         alert('class is scheduled with same information, try with other');    
        }
        else{
        $('#local_timetable_timelayout').html(response);        
         if (form1) {
            console.log(form1);
            //form1.dialog("close");
             form1.dialog("close");
         }
         else         
          dialog.dialog("close");       
        }
    });

    request.fail(function (jqXHR, textStatus) {
        alert("Request failed: " + textStatus);
    });

}// end of function



//------------- from addbutton--------------------
function schedule_dialog(slotid, dayname) {

     $(".timetable_customarea").hide();
     $("." + slotid + dayname + "form")[0].reset();
  
    dialog = $("." + slotid + dayname + "dialog-form").dialog({
        autoOpen: false,
        height: 500,
        width: 500,
        modal: true,
        close: function () {
            // ---used to reset the form
           //$("." + slotid + dayname + "form")[0].reset();
           $(".timetable_customarea").hide();     
           $(".timetable_scheduledarea").show();
            
        },
        create: function(event, ui) { 
            var widget = $(this).dialog("widget");
            $(".ui-dialog-titlebar-close span", widget)
            $("span:last-child").remove();
        }
    });

    dialog.dialog("open");
    
    dialog.find("." + slotid + dayname + "form").on("submit", function (event) {
        event.preventDefault();
        //  var str1='';
        //formData = JSON.parse($("#floorplan-form").serializeArray());
        var ss=$(this).serializeArray();
       // alert(ss);
        var str1 = jQuery("." + slotid + dayname + "form").serializeArray();

      //  alert(str1);
        var str = JSON.stringify(str1);        

        var customfield=0;
        $.each(str1, function (i, item) {
        if (item.name == 'customfield') {
            customfield = item.value;
        }
       });

       if (customfield==1) 
        custom_form_validation(str);       
       else
        form_validation(str);
    });
}



function schedule_dialog_edit(scheduleid,slotid) {
    // alert('hi');
    
    var request = $.ajax({
        url: "ajaxdialog.php",
        method: "POST",
        data: {scheduleid: scheduleid , slotid:slotid},
        dataType: "html"
    });

    request.done(function (res) {
         var str=''; var str1='';  var form1='';
        var tag = $("<div></div>");
         form1= tag.html(res);
        form1.dialog({
                modal: true,
                title: "Edit Class Scheduled information",
                width: 600,
                height: 450,
                close: function () {
                    // ---used to reset the form
                    //alert('closing edit form');
                    document.getElementById("." +scheduleid+'form').reset();
                   //  $("." +scheduleid+'form')[0].reset();
                    //$("." + slotid + dayname + "form")[0].reset();
                },
                create: function(event, ui) { 
                    var widget = $(this).dialog("widget");
                    $(".ui-dialog-titlebar-close span", widget)
                    $("span:last-child").remove();
                }
            }).dialog('open');
        form1.find("." + scheduleid + "form").on("submit", function (event) {
            event.preventDefault();     
             str1 = jQuery("." + scheduleid + "form").serializeArray();            
             $(this)[0].reset();
             str = JSON.stringify(str1);             
             var customfield=0;
            $.each(str1, function (i, item) {
            if (item.name == 'customfield') {
                customfield = item.value;
            }
            });
    
            if (customfield==1) 
            custom_form_validation(str, form1);       
            else
            form_validation(str, form1);
        });        
        
        
    });

    request.fail(function (jqXHR, textStatus) {
        alert("Request failed: " + textStatus);
    });
}




// -------getting custom values---------
function get_customvalue(id,dayname){

    $(".timetable_customarea").hide();    
    if($(".customarea"+id+dayname).is(':checked')){        
     $(".timetable_customarea").show();  // checked
     $(".timetable_scheduledarea").hide();
     $("#customfield"+id+dayname).val('1'); 

  
    // $(".timeintervals_dialog").attr('disabled','disabled');
     
    }
    else{
        
      $(".timetable_customarea").hide();     
      $(".timetable_scheduledarea").show();  // unchecked
      $("#customfield"+id+dayname).val('0');
     // $(".timeintervals_dialog").removeAttr('disabled');
    
    }   
    
} // end of function


// ----getting instructor list from  ajax file--------------------
function get_instructor(timeintervalid, dayname, selected) {

    var request = $.ajax({
        url: "timelayout_ajax.php",
        method: "POST",
        data: {classid: selected.value},
        dataType: "html"
    });

    request.done(function (instructorselect) {
        $("." + timeintervalid + dayname + "instructor").html(instructorselect);
    });

    request.fail(function (jqXHR, textStatus) {
        alert("Request failed: " + textStatus);
    });


}
