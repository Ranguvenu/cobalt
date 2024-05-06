$(document).ready(function() {
	$('#page-local-admissions-personal_information #id_cancel').on('click', function(){
		var type = location.search.split('format=')[1];
		if (type == 'card') {
			window.location.href = M.cfg.wwwroot+'/local/admissions/programs.php?format='+type;
		} else if (type == 'list') {
			window.location.href = M.cfg.wwwroot+'/local/admissions/index.php?format='+type;
		} else {
			window.location.href = M.cfg.wwwroot+'/local/admissions/view.php';
		}
	});
});
