require(['jquery', 'core/yui'], function($) {
     $("#pdf").click(function(){ 
        var element = $(this);
        var data = element.val();
        alert(data);
       	location.href= M.cfg.wwwroot+'/blocks/proposals/preview.php';
        
        });
   });