/**
 * Streaming video js
 *
 * @module     block_stream/stream
 * @class      stream
 * @package    block_stream
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
define(['jquery', 
        'mod_stream/jquery.dataTables',
        'core/str'], 
        function($,DataTable, Str){
    return {
    	init: function() {
			var self=this;
			$( ".dataTable" ).each(function( index ) {
				viewDatatble = self.DataTables(this.id);
			});
      	},
      	DataTables: function(container){
			var str = $('#'+container).data('function');
			var pagelenth = $('#'+container).data('pagelength');
			if(pagelenth == undefined){
			 pagelenth = 10;
			}
			var args = {action: str};
			return Str.get_strings([{
                  key: $('#'+container).data('nodatastring'),
                  component: 'block_stream',
                  param: M.cfg.sesskey
          	}]).then(function(s) {
				$('#'+container).DataTable({
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
				    // "ajax": M.cfg.wwwroot + "/mod/stream/reportfinder.php?action="+str +"&id="+id +"&type="+type,
				    'ajax': {
				            "type": "POST",
				            "dataType": "json",
				            "url": M.cfg.wwwroot + '/lib/ajax/service.php?sesskey=' + M.cfg.sesskey+'&info=block_stream_tablecontent',
				            "data": function(d) {
				              newdata = {};
				              newdata.methodname = 'block_stream_tablecontent';  
				              newdata.args = {args: JSON.stringify({d,args})};
				            return JSON.stringify([newdata]);
				        },
				        "dataSrc" : function (json) {
				          var data = JSON.parse(json[0].data.data);
				          return data;
				        }
				    },
					"language": {
                	    "search": '',
					    "searchPlaceholder": 'search',
					    "paginate": {
					      "next": '<i class="fa fa-angle-right"></i>',
					      "previous": '<i class="fa fa-angle-left"></i>' 
					    }
					}
				});
			});
		}, 
    }
});
