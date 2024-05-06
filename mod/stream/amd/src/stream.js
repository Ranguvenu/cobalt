/**
 * Streaming video js
 *
 * @module     mod_stream/stream
 * @class      stream
 * @package    mod_stream
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
define(['jquery',
        'media_videojs/video-lazy',
        'mod_stream/jquery.dataTables', 'mod_stream/flatpickr', 'mod_stream/radioslider', 'core/modal_factory', 'core/modal_events', 'core/fragment', 'core/templates', 'core/ajax', 'core/str'],
        function($, videojs, DataTable, flatpickr, RadiosToSlider, ModalFactory, ModalEvents, Fragment, Templates, Ajax, Str){
    var tables = [];
    return {
        init: function() {
            var self=this;
            $( ".dataTable" ).each(function( index ) {
                viewDatatble = self.DataTables(this.id);
            });

          $(document).on('graphsReload', '#segmented-button',function(){
              $( ".dataTable" ).each(function( index ) {
                console.log(this);
               var thisDatatble = self.DataTables(this.id);
              var timestamps = $(this).data('timestamps');
                tables[$(this).attr('id')].columns(0).search(timestamps).draw();
            });
          });

           flatpickr('#customrange',{
              mode: 'range',
              onClose: function(selectedDates, dateStr, instance) {
                  var enddate = selectedDates[1].getTime() / 1000 + 86400;
                  var timestamps = selectedDates[0].getTime() / 1000+'-'+enddate;
                  // viewDatatble.columns(0).search(timestamps).draw();
                  // coursestatsDatatble.columns(0).search(timestamps).draw();
                  // summaryDatatble.columns(0).search(timestamps).draw();
                  // activitystatusDatatble.columns(0).search(timestamps).draw();
                  $('#segmented-button').data('timestamps', timestamps);
                  $('#segmented-button').trigger('graphsReload');
              }
          });

          var radios = RadiosToSlider.init($('#segmented-button'), {
              size: 'medium',
              animation: true,
          });

          $(document).on('radiochange', '#segmented-button', function(){
            var checkedData = $('#segmented-button').find('input:checked').attr('value');
            var today = new Date();
            var endDate = new Date(today.getFullYear(), today.getMonth(), today.getDate());
            var start_duration = '';
            $('#customrange').hide();
            var check = true;
            switch(checkedData){
              case 'week':
                  start_duration = new Date(today.getFullYear(), today.getMonth(), today.getDate() - 7);
                  break;
              case 'month':
                  start_duration = new Date(today.getFullYear(), today.getMonth() - 1, today.getDate());
                  break;
              case 'year':
                  start_duration = new Date(today.getFullYear() - 1, today.getMonth(), today.getDate());
                  break;
              case 'custom':
                  $('#customrange').show();
                  return;
                break;
                case 'clear':
                  check = false;
                break;
              default:
                  break;
            }
            if(check){
              var timestamps = start_duration.getTime()/1000+'-'+endDate.getTime()/1000;
            }else{
              var timestamps = '0-0';
            }

            viewDatatble.columns(0).search(timestamps).draw();
            $('#segmented-button').data('timestamps', timestamps);
            $('#segmented-button').trigger('graphsReload');
          });
        $(document).on('change', '#module_select_filter', function(){
          var courseid = $('#course_select_filter').children("option:selected").val();
          window.location.href = M.cfg.wwwroot+'/mod/stream/'+$(this).children("option:selected").data('page')+'?id='+courseid+'&cmid='+$(this).children("option:selected").val();
        });
        $(document).on('change', '#course_select_filter', function(){
          window.location.href = M.cfg.wwwroot+'/mod/stream/'+$(this).children("option:selected").data('page')+'?id='+$(this).children("option:selected").val();
        });

      },
      DataTables: function(container){
       var str = $('#'+container).data('function');
       var id = $('#'+container).data('id');
       var type = $('#'+container).data('type');
       var pagelenth = $('#'+container).data('pagelength');
       if(pagelenth == undefined){
         pagelenth = 10;
       }
       var args = {action: str,id: id,type: type};
       console.log(container);
       return tables[container] = $('#'+container).DataTable({
            'bInfo' : false,
                'bLengthChange': false,
                'language': {
                        'paginate': {
                            'next': '>',
                            'previous': '<'
                        }
                },
            'pageLength': pagelenth,
            'processing': true,
            'serverSide': true,
            "retrieve": true,
            // "ajax": M.cfg.wwwroot + "/mod/stream/reportfinder.php?action="+str +"&id="+id +"&type="+type,
            'ajax': {
                    "type": "POST",
                    "dataType": "json",
                    "url": M.cfg.wwwroot + '/lib/ajax/service.php?sesskey=' + M.cfg.sesskey + '&info=mod_stream_tablecontent',
                    "data": function(d) {
                      newdata = {};
                      newdata.methodname = 'mod_stream_tablecontent';
                      args.timestamps = $("#segmented-button").data('timestamps');
                      newdata.args = {args: JSON.stringify({d,args})};
                    return JSON.stringify([newdata]);
                },
                "dataSrc" : function (json) {
                  var data = JSON.parse(json[0].data.data);
                  return data;
                }
            },
        }).on( 'draw.dt', function () {
            if(str == 'getSummaryReport'){
                $('.rating-1').each(function(){
                    var stars = $(this).data('stars');
                    for (var i = 1; i <= 5; i++){
                        var icon = $("<i>", {class : 'fa fa-star', style : "font-size: 16px; margin-left: 1px; color:" + (i <= stars ? "#f1c40f": "#ecf0f1")});
                        $(this).append(icon);
                    }
                });
            }
        });
      },
      DrillDownReportPopup: function(){
          $(document).on('click', '.drilldownreportpopup', function(){
            var data = $(this).data();
            var container = $(this).data('function') + 'table' + $(this).data('id');
            var timestamps = $("#segmented-button").data('timestamps');
            if(timestamps != undefined) {
                data.timestamps = timestamps;
            }
            if(data.email != undefined) {
                var modaltitle = data.reportname + '(' + data.email +')';
            } else{
                var modaltitle = data.reportname;
            }
            var bodyElement = Fragment.loadFragment('mod_stream', 'drilldownreport', 1, data);
            return Str.get_strings([{
                  key: 'tablesearch',
                  component: 'stream'
            }]).then(function(s) {
            ModalFactory.create({
                title: modaltitle,
                type: ModalFactory.types.DEFAULT,
                body: bodyElement
            }).done(function(modal) {
                this.modal = modal;
                this.modal.getRoot().addClass('drilldown'+data.function);
                this.modal.setLarge();
                modal.show();
                this.modal.getRoot().on(ModalEvents.bodyRendered, function() {

                     $('#'+container).DataTable({
                      destroy: true,
              "language": {
                          "search": '<i class="fa fa-search"></i>',
                          "searchPlaceholder": s[0],
                          "paginate": {
                              "previous": '<i class="fa fa-angle-left"></i>',
                              "next": '<i class="fa fa-angle-right"></i>'
                          },
                       },
                      "bInfo": false,
                      pageLength: 10
                     });
                }.bind(this));
            }.bind(this));
          });
          });
        }
    }
});
