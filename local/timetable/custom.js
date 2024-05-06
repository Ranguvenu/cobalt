$(document).ready(function () {

    $('#calendar').fullCalendar({
        header: {
            left: 'prev,next today',
            center: 'title',
            right: 'month,agendaWeek,agendaDay'
        },
        weekends: true,
        theme: Boolean,
        //  slotDuration: '00:30:00',
        slotDuration: '00:30:00',
        minTime: '08:00:00',
        maxTime: '19:00:00',
        allDaySlot: false,
        columnFormat: {
            month: 'ddd',
            week: 'ddd D/MMM',
            day: 'dddd'
        },
         
         axisFormat: 'h:mm', 

        defaultDate: new Date(),
        selectable: true,
        defaultView: 'agendaWeek',
//        events: [
//    {
//        "title": "Calendar Test",
//        "start": "2015-06-10T13:30:00",
//        "end": "2015-06-10T14:00:00",
//        "allDay": false
//    }
//],
     //   hiddenDays:[0],
        events: "events.php",
        cache: true,
        eventRender: function(event, element)
        { 
         var des ="<br/>" + event.instructor+"</br>";
             if(event.classroom =="")       
             des += "Class Room: ---- ";
             else
             des += event.classroom;    
        element.find('.fc-title').append("<br/>" + event.instructor+"</br>"+ "Class Room: "+event.classroom); 
        },
        eventRender: function (event, element) {
            //   element.attr('href', 'javascript:void(0);');           
            element.attr("instructor", event.instructor);
            element.attr("classroom", event.classroom);
            element.click(function () {
                var des = '<b> Scheduled Time:</b></br> <b>From</b> ' + moment(event.start).format('MMM Do h:mm A') + '<b> To</b> ' + moment(event.end).format(' h:mm A') + '</br>';
                des += '<b>Instructor:</b> ' + event.instructor + '</br>';
                des += '<b>Classroom:</b> ' + event.classroom + '</br>';
                if (event.attendance) {                 
                    if (event.action > 0) {                     
                           des += '<b><a href= "' + M.cfg.wwwroot + '/local/attendance/take.php?classid=' + event.id + '&shid='+event.scheduleid+'&time=' + event.starttime + '&date='+moment(event.start).format('YYYY-M-D')+'">Edit attendance<img src=' + M.cfg.wwwroot + '/local/attendance/pix/icon.gif' + '></img></a> ';  
                        }
                        else
                        des += '<b><a href= "' + M.cfg.wwwroot + '/local/attendance/take.php?classid=' + event.id + '&shid='+event.scheduleid+'&time=' + event.starttime + '&date='+moment(event.start).format('YYYY-M-D')+'">Take attendance<img src=' + M.cfg.wwwroot + '/local/attendance/pix/icon.gif' + '></img></a> ';                     
                    
                  
                }
        
                $("#eventInfo").html(des);
                $("#eventContent").dialog({modal: true, title: event.title, width: 270, position: {my: "left top", at: "left bottom", of: element}});
        
            });
        
            if (event.attendance)
                if (event.action == 0 ) 
               // element.append('<span id="customevent"><a href= "' + M.cfg.wwwroot + '/local/attendance/take.php?classid=' + event.id + '&time=' + event.starttime + '&date='+moment(event.start).format('YYYY-M-D')+'">Take attendance<img src=' + M.cfg.wwwroot + '/local/attendance/pix/icon.gif' + '></img></a></span>');
                element.append('<span id="customevent">Take attendance<img src=' + M.cfg.wwwroot + '/local/attendance/pix/icon.gif' + '></img></span>');
                if (event.action > 0 ) 
                element.append('<span id="customevent">Edit attendance <img src=' + M.cfg.wwwroot + '/local/attendance/pix/icon.gif' + '></img></span>');
        },
        //eventRender: function(event, element) {

        //},
        loading: function (bool) {
            //  alert("hi");
            if (bool)
                $('#loading').show();
            else
                $('#loading').hide();
        }


    });

    $('#datepicker').datepicker({
        inline: true,
        onSelect: function (dateText, inst) {
            var d = new Date(dateText);
            $('#calendar').fullCalendar('gotoDate', d);
        }
    });

});



/*eventRender: function (event, element) {
            //   element.attr('href', 'javascript:void(0);');           
            element.attr("instructor", event.instructor);
            element.attr("classroom", event.classroom);
            element.click(function () {
                var des = '<b> Scheduled Time:</b></br> <b>From</b> ' + moment(event.start).format('MMM Do h:mm A') + '<b> To</b> ' + moment(event.end).format(' h:mm A') + '</br>';
                des += '<b>Instructor:</b> ' + event.instructor + '</br>';
                des += '<b>Classroom:</b> ' + event.classroom + '</br>';
                if (event.attendance) {                 
                    if (event.today <= 0) {
                        if (event.today ==0 ) {
                           des += '<b><a href= "' + M.cfg.wwwroot + '/local/attendance/take.php?classid=' + event.id + '&time=' + event.starttime + '&date='+moment(event.start).format('YYYY-M-D')+'">take attendance<img src=' + M.cfg.wwwroot + '/local/attendance/pix/icon.gif' + '></img></a> ';  
                        }
                        else{
                         if (event.today < 0 )   
                        des += '<b><a href= "' + M.cfg.wwwroot + '/local/attendance/take.php?classid=' + event.id + '&time=' + event.starttime + '&date='+moment(event.start).format('YYYY-M-D')+'">Edit attendance<img src=' + M.cfg.wwwroot + '/local/attendance/pix/icon.gif' + '></img></a> ';
                       }
                    }
                    else
                        //des += '<span> you cant take  upcoming days attendance </span>';
                        des += '<b><a href= "' + M.cfg.wwwroot + '/local/attendance/take.php?classid=' + event.id + '&time=' + event.starttime + '&date='+moment(event.start).format('YYYY-M-D')+'">Edit attendance<img src=' + M.cfg.wwwroot + '/local/attendance/pix/icon.gif' + '></img></a> ';
                }
        
                $("#eventInfo").html(des);
                $("#eventContent").dialog({modal: true, title: event.title, width: 350, position: {my: "left top", at: "left bottom", of: element}});
        
            });
        
            if (event.attendance)
                if (event.today == 0 ) 
                element.append('<span id="customevent">Take attendance <img src=' + M.cfg.wwwroot + '/local/attendance/pix/icon.gif' + '></img></span>');
                if (event.today < 0 ) 
                element.append('<span id="customevent">Edit attendance <img src=' + M.cfg.wwwroot + '/local/attendance/pix/icon.gif' + '></img></span>');
        }, */