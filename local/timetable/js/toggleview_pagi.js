

$(document).ready(function() {
  $(".js-example-basic-single").select2();
});


 function teammember_list(teammanagerid) {
    // alert('hi');
    // alert(teammanagerid);
    YUI().use('node','transition', function(Y) {
	node = Y.one("#dialog"+teammanagerid+"");
            node.toggleView();
    });
}


$(function() {
    $('#tmselect').change(function() {
        $('#tmform').submit();
    });
});