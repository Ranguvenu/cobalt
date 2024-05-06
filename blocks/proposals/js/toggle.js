require(['jquery', 'core/yui'], function($) {
   $("#id_funded_1").prop("checked", false);
   $("#id_funded_0").prop("checked", false);
   $("#id_type_0").prop("checked", false);

    $( "#id_funded_1" ).prop( "disabled", true );
    $( "#id_funded_0" ).prop( "disabled", true );

    $("#id_type_0").click(function(){ 
      var element = $(this);
      var data = element.val();
      if (data == 0) {
         $( "#id_funded_1" ).prop( "disabled", true );
         $( "#id_funded_0" ).prop( "disabled", true );
         location.href= M.cfg.wwwroot+'/blocks/proposals/nonfunded.php';
      }
   });
    $("#id_type_1").click(function(){ 
      var element = $(this);
      var data = element.val();
     
      if (data == 1) {
         $( "#id_funded_1" ).prop( "disabled", false);
         $( "#id_funded_0" ).prop( "disabled", false);
      }
   });
   $("#id_funded_0").click(function(){
      var element = $(this);
      var data = element.val();
      if (data == 0) {
         location.href= M.cfg.wwwroot+'/blocks/proposals/fundedres.php';
      }
   });
   $("#id_funded_1").click(function(){ 
      var element = $(this);
      var data = element.val();
      if (data == 1) {
         location.href= M.cfg.wwwroot+'/blocks/proposals/funded.php';
      }
   });
});

