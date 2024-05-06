$(document).ready(function() {
	$('#id_subject').keypress(function(e){
		if (e.which == 32) {
	        var subject = $('#id_subject').val();
	        var data = subject.trim().length;
		    if (data == 0) {
				$('.invalid-feedback').css('display', 'block');
				$('#id_subject').addClass('is-invalid');
		    	$('#id_error_subject').html('- Blank spaces are not allowed.');
				return false;
		    }
		}
    });
    $('#id_description').keypress(function(e){
		if (e.which == 32) {
	        var subject = $('#id_description').val();
	        var data = subject.trim().length;
		    if (data == 0) {
				$('.invalid-feedback').css('display', 'block');
				$('#id_description').addClass('is-invalid');
		    	$('#id_error_description').html('- Blank spaces are not allowed.');
				return false;
		    }
		}
    });
});

function mycommentpopupform(queryid){

	$('#basicModal'+queryid).dialog({
		title: 'Post Reply',
		dialogClass: 'block_queries_popup',
	  	modal: false
	});
	$Adduiclass = $('#basicModal'+queryid).closest(".ui-dialog").find(".ui-dialog-titlebar-close").removeClass('ui-button').addClass('ui-button query-close');
	$uititle = $('#basicModal'+queryid).closest(".ui-dialog").find(".ui-dialog-titlebar-close").html("<span class='ui-button-icon-primary ui-icon ui-icon-closethick'></span>");
}

// Code for the display all comments.
function viewresponses(id){
	$('.student'+id).slideToggle('fast');
	if($('.student'+id).css('display') != 'none') {
		$(this).find('.dataTables_wrapper').css('display', 'block');
	} else {
		$(this).find('.dataTables_wrapper').css('display', 'none');
	}
}
