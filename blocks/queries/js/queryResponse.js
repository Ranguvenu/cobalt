$(document).ready( function () {
// $('#queryresponse').DataTable();
});
function view(id) {
    if($('.toggle'+id).is(':visible'))
       $('.toggle'+id).css('display', 'none');
    else if($('.toggle'+id).is(':hidden'))
       $('.toggle'+id).css('display', 'block');
  }
function formvalidate(queryid) {
    var comment = $('#comments'+queryid).val();
    var data = comment.trim();
    if(data == undefined || data == ''){
        $('.error_box'+queryid).html('<p style=\'color:red;\'>Please fill the required fields</p>');
        return false;
    }
}
