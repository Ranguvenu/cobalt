/**
 * Add a create new group modal to the page.
 *
 * @module     local/Attendance
 * @class      Attendance
 * @copyright  2023 Dipanshu Kasera
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
define(['jquery'], function($) {
    $(document).ready(function(){
        $('.takegrid .status .attendancestatus').on('click', function() {
            var classes = $(this).attr('class');
            var res = classes.split(" ");

            var attclass = res[1];
            var myclass = res[2];
            var uclass = res[3];
            var attid = myclass.replace('attstatus', '');
            var userid = uclass.replace('u', '');

            if (attclass == 'present') {
                var color = 'rgb(255, 255, 255)';
                var id = attid;
            } else if (attclass == 'absent') {
                var color = 'rgb(255, 255, 254)';
                var id = attid;
            } else {
                var color = 'rgb(255, 255, 253)';
                var id = attid;
            }

            $('.generaltable.takegrid td.userwithoutenrol'+userid).css('background-color', color);
            $('.generaltable.takegrid td.userwithoutenrol'+userid+' input[name="user'+userid+'"]').val(id);

            if (attclass == 'present') {
                $('.generaltable.takegrid td.userwithoutenrol'+userid).removeClass('absent');
                $('.generaltable.takegrid td.userwithoutenrol'+userid).removeClass('late');
                $('.generaltable.takegrid td.userwithoutenrol'+userid).addClass('present');
            }
            if (attclass == 'absent') {
                $('.generaltable.takegrid td.userwithoutenrol'+userid).removeClass('present');
                $('.generaltable.takegrid td.userwithoutenrol'+userid).removeClass('late');
                $('.generaltable.takegrid td.userwithoutenrol'+userid).addClass('absent');
            }
            if (attclass == 'late') {
                $('.generaltable.takegrid td.userwithoutenrol'+userid).removeClass('absent');
                $('.generaltable.takegrid td.userwithoutenrol'+userid).removeClass('absent');
                $('.generaltable.takegrid td.userwithoutenrol'+userid).addClass('late');
            }

            if(attclass == 'late'){
                $('#representation_comment'+userid).css('display', 'block');
            } else {
                $('#representation_comment'+userid).css('display', 'none');
            }

            $(this).find('input[type="text"]').click(function(event){
                event.stopPropagation();
            });

            $(this).find('input[type="text"]').focusout(function(){
                $('.takegrid .userwithoutenrol'+userid).on("click");
            });
        });
        // $('.takegrid .userwithoutenrol').on('click', function(){
        //     var classes = $(this).attr('class');
        //     var res = classes.split(" ");
        //     var myclass = res[1];
        //     var userid = myclass.replace('userwithoutenrol', '');

        //     var statuses = $('.jsonstatus'+userid).val();

        //     var obj = $.parseJSON( statuses );
        //     var statuss = new Array();
        //     $.each(obj, function(key, value){
        //         console.log(value.id);
        //         statuss[value.acronym] = value.id;
        //     });

        //     var color = $('.generaltable.takegrid td.'+myclass).css('background-color');
        //     var colors = [
        //                     "novalue", "rgb(255, 255, 255)",
        //                     "rgb(255, 255, 254)",
        //                     "rgb(255, 255, 253)",
        //                     /*"rgb(193, 154, 107)",*/
        //                     "rgb(255, 255, 255)"
        //                 ];
        //     var current = $.inArray(color, colors);

        //     if (current !== 0) {
        //         //code
        //         var nextcolor = current+1;
        //     }

        //     var display = ["", "P", "A", "L", /*"E",*/ "P"];

        //     $('.generaltable.takegrid td.'+myclass).css('background-color', colors[nextcolor]);
        //     $('.generaltable.takegrid td.'+myclass+' input[name="user'+userid+'"]').val(statuss[display[nextcolor]]);

        //     if (display[nextcolor] === 'P') {
        //         $('.generaltable.takegrid td.'+myclass).removeClass('absent');
        //         $('.generaltable.takegrid td.'+myclass).removeClass('late');
        //         $('.generaltable.takegrid td.'+myclass).addClass('present');
        //     } else if (display[nextcolor] === 'A') {
        //         $('.generaltable.takegrid td.'+myclass).removeClass('present');
        //         $('.generaltable.takegrid td.'+myclass).removeClass('late');
        //         $('.generaltable.takegrid td.'+myclass).addClass('absent');
        //     } else if (display[nextcolor] === 'L') {
        //         $('.generaltable.takegrid td.'+myclass).removeClass('present');
        //         $('.generaltable.takegrid td.'+myclass).removeClass('absent');
        //         $('.generaltable.takegrid td.'+myclass).addClass('late');
        //     }

        //     if(display[nextcolor] === "L"){
        //         $('#representation_comment'+userid).css('display', 'block');
        //     } else {
        //         $('#representation_comment'+userid).css('display', 'none');
        //     }

        //     $(this).find('input[type="text"]').click(function(event){
        //         event.stopPropagation();
        //     });

        //     $(this).find('input[type="text"]').focusout(function(){
        //         $('.takegrid .userwithoutenrol'+userid).on("click");
        //     });
        // });

        // Don't un-comment below code

        // var value = $('.orange').prop('class');
        // var val = value.slice(-17);
        // var userid = val.slice(0, 2);

        // if($('.attendancestatus')[2].outerText === "L"){
        //     $('#representation_comment'+userid).css('display', 'block');
        // } else {
        //     $('#representation_comment'+userid).css('display', 'none');
        // }

        $('.takegrid .userwithoutenrol').click(function(){
            $(this).on("click");
        });
    });

    $(document).ready(function () {
        var disable_sendmsg = function () {
            var checked = $("input:checked").length;
            if (checked == '0') {
                $('.sendmsg_button').attr('disabled', 'disabled');
            } else {
                $('.sendmsg_button').removeAttr('disabled');
            }
        };
        disable_sendmsg();
        $(".checkbox").on("click", disable_sendmsg);
    });

    return function () {};

});
