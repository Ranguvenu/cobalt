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
        'mod_stream/jquery.dataTables', 
        'mod_stream/flatpickr', 
        'mod_stream/radioslider', 
        'core/modal_factory', 
        'core/modal_events', 
        'core/fragment', 
        'core/templates', 
        'core/ajax',
        'core/str'], 
        function($,videojs, DataTable, flatpickr, RadiosToSlider, ModalFactory, ModalEvents, Fragment, Templates, Ajax, Str){
            return {
                LineChart: function(container, reportdata) {
                  if(!document.getElementById(container)){
                    return false;
                  }
                    Highcharts.chart(container, {
                            title: {
                                text: ''
                            },
                            xAxis: {
                                categories: reportdata[0]
                            },
                            plotOptions: {
                                line: {
                                    dataLabels: {
                                        enabled: true
                                    },
                                    enableMouseTracking: false
                                }
                            },
                            series:  reportdata[1]
                            
                        });
                },
                SparklineChart: function(container, reportdata) {
                  if(!document.getElementById(container)){
                    return false;
                  }
                   Highcharts.chart(container, {
                      chart: {
                        type: 'column',
                        width:300,
                        height: 75
                      },
                      title: {
                              text: ''
                            },
                      xAxis: {
                         categories: reportdata[0][0].datesinfo
                      },
                      credits: {
                          enabled: false
                      },
                      legend: {
                          enabled: false
                      },
                      yAxis: {
                          visible: false,
                      },
                      plotOptions: {
                          column: {
                              dataLabels: {
                                  enabled: false
                              },
                              legend: {
                                enabled: false
                              },
                              enableMouseTracking: false
                          }
                      },
                    series:  reportdata[0],
                    exporting: {
                            enabled: false
                        }
                         });
                 },
                 ReportsCharts: function(){
                    var completedvideosbyusers = JSON.parse(stream_reports_data.completedvideosbyusers);
                    var completedusersbyvideos = JSON.parse(stream_reports_data.completedusersbyvideos);
                    var toplikes = JSON.parse(stream_reports_data.toplikes);
                    var topratings = JSON.parse(stream_reports_data.topratings);
                    var topviews = JSON.parse(stream_reports_data.topviews);
                    var weekvideos = JSON.parse(stream_reports_data.weekvideos);
                    var weekminutes = JSON.parse(stream_reports_data.weekminutes);
                    var weekviews = JSON.parse(stream_reports_data.weekviews);
                    var weeklikesdislikes = JSON.parse(stream_reports_data.weeklikesdislikes);

                    var weekratings = JSON.parse(stream_reports_data.weekratings);


                    this.LineChart('toplikes', toplikes);
                    this.LineChart('topratings', topratings);
                    this.LineChart('topviews', topviews);

                    this.SparklineChart('activevideos', weekvideos);
                    this.SparklineChart('streamedvideos', weekminutes);
                    this.SparklineChart('totalviews', weekviews);
                    this.SparklineChart('likesdislikes', weeklikesdislikes);

                    this.SparklineChart('ratings', weekratings);

                    var container2 = $('#container2');
                    if(container2.length){
                      return Str.get_strings([{
                      key: 'noofusers',
                      component: 'stream'
                  }]).then(function(s) {
                      var completedUsersbyVideoGraph = Highcharts.chart('container2', {
                          title: {
                              text: ''
                          },
                          xAxis: {
                              categories: completedusersbyvideos[0],
                              labels: {
                                  rotation: -45,
                                  style: {
                                      fontSize: '13px',
                                  }
                              }
                          },
                          yAxis: {
                              min: 0,
                              title: {
                                  text: s[0]
                              }
                          },
                          legend: {
                              enabled: true
                          },
                          series: completedusersbyvideos[1]
                      });
                    });
                      $(document).on('click', '.togglecontaier1', function(){
                        var  chartContainer = $("#container1");
                        if($(this).data('reporttype') == 'table'){
                          if (chartContainer.is(":visible")) {
                            if (!completedVideosbyUsersGraph.dataTableDiv) {
                              completedVideosbyUsersGraph.update({
                                  exporting: {
                                      showTable: true
                                  }
                              });
                            } else {
                                $(completedVideosbyUsersGraph.dataTableDiv).show();
                            }
                          }
                          chartContainer.hide();
                        }else{
                          chartContainer.show();
                          completedVideosbyUsersGraph.update({
                              exporting: {
                                  showTable: false
                              }
                          });
                          $(completedVideosbyUsersGraph.dataTableDiv).hide();
                        }
                      });
                      $(document).on('click', '.togglecontaier2', function(){
                        var  chartContainer = $("#container2");
                        if($(this).data('reporttype') == 'table'){

                            // completedUsersbyVideoGraph.dataTableDiv.classList.add('modal fade');

                          if (chartContainer.is(":visible")) {
                            if (!completedUsersbyVideoGraph.dataTableDiv) {
                              completedUsersbyVideoGraph.update({
                                  exporting: {
                                      showTable: true
                                  }
                              });
                            } else {
                                $(completedUsersbyVideoGraph.dataTableDiv).show();
                            }
                          }
                          chartContainer.hide();
                        }else{
                          chartContainer.show();
                          completedUsersbyVideoGraph.update({
                              exporting: {
                                  showTable: false
                              }
                          });
                          $(completedUsersbyVideoGraph.dataTableDiv).hide();
                        }
                      });
                      $(document).on('graphsReload', '#segmented-button', function(){
                        var timestamps = $("#segmented-button").data('timestamps');
                        var courseid = $("#segmented-button").data('course');
                        // console.log(data);
                        var promise = Ajax.call([{
                            methodname: 'mod_stream_completedVideosbyUsersGraph',
                            args: {timestamps: timestamps, courseid: courseid}
                        }]);
                        promise[0].done(function(data){
                            var data = JSON.parse(data);

                            completedUsersbyVideoGraph.update({
                              xAxis: {
                                categories: data.completedusersbyvideos['categories'],
                              },
                              series: data.completedusersbyvideos['data'],
                            });
                        });
                        var promise = Ajax.call([{
                          methodname : 'mod_stream_topVideosInfo',
                                args : {courseid: courseid, timestamps: timestamps}
                        }]);
                          promise[0].done(function(data){
                          var data = JSON.parse(data);
                            Templates.render('mod_stream/topReports', data).then(function(html, js) {
                              $('#topreportsInfo').html(html);
                            });
                        });
                        var promise = Ajax.call([{
                          methodname : 'mod_stream_durationbasedinfo',
                                args : {courseid: courseid, timestamps: timestamps}
                        }]);
                        promise[0].done(function(data){
                          var data = JSON.parse(data);
                            Templates.render('mod_stream/durationbased', data).then(function(html, js) {
                              $('#durationbasedinfo').html(html);
                            });
                        });
                      });
                }
                      $(document).on('graphsReload', '#segmented-button', function(){
                        var timestamps = $("#segmented-button").data('timestamps');
                        var courseid = $("#segmented-button").data('course');
                        var action = $("#segmented-button").data('action');
                        var moduleid = $("#segmented-button").data('moduleid');
                        if(moduleid == undefined){
                          moduleid = '';
                        }
                                var params = {timestamps: timestamps};
                                if(action == 'reportsHeader'){
                                  params.courseid = courseid;
                                }else{
                                  params.moduleid = courseid;
                                }
                                var promise = Ajax.call([{
                                  methodname : 'mod_stream_'+action,
                          args: params
                                }]);
                                promise[0].done(function(data){
                                  var data = JSON.parse(data);
                                  Templates.render('mod_stream/reportsHeading', data).then(function(html, js) {
                                      $('#reportsHeader').html(html);
                                  });
                                });
                          });
                        $( ".timeperiod" ).change(function() {
                            var value = $(this).val();
                            var courseid = $(this).data('courseid');
                            var promises = Ajax.call([{
                                methodname: 'stream_timeperiod',
                                args: { timeperiod: value, courseid:courseid },
                            }])

                            promises[0].done(function(response) {
                               var dt =  JSON.parse(response.timeperiod);
                               var table = '';
                                $.each(dt, function( index, value) {
                                    table  += '<tr><td>' + value.fullname +"</td><td>" + value.completedvideos +"</td></tr>";
                                });
                                $(".timeperioddata").html(table);
                            }).fail(function(ex) {
                                console.log(ex);
                            });
                        });
          var container3 = $('#container3');
          if(container3.length){
              var completedusersbyvideos = JSON.parse(stream_reports_data.dailyvisits);
              return Str.get_strings([{
                  key: 'dailyhitsviews',
                  component: 'stream'
              },
              {
                  key: 'hitsviews',
                  component: 'stream'
              }]).then(function(s) {
              var container3 = Highcharts.chart('container3', {
                  chart: {
                      type: 'line'
                  },
                  title: {
                      text: s[0]
                  },
                  xAxis: {
                      type: 'category',
                      labels: {
                          rotation: -45,
                          style: {
                              fontSize: '13px',
                          }
                      }
                  },
                  exporting: {
                    showTable: false,
                    csv: {
                     
                    }
                  },
                  yAxis: {
                      min: 0,
                      title: {
                          text: s[1]
                      }
                  },
                  legend: {
                      enabled: false
                  },
                  series: [{
                      name: s[1],
                      data: completedusersbyvideos,
                      dataLabels: {
                          enabled: true,
                          rotation: -90,
                          color: '#FFFFFF',
                          align: 'right',
                          format: '{point.y}', // one decimal
                          y: 10, // 10 pixels down from the top
                          style: {
                            fontSize: '13px',
                            fontFamily: 'Verdana, sans-serif'
                          }
                      }
                  }]
              });
              });
              $(document).on('click', '.togglecontaier3', function(){
                var  chartContainer = $("#container3");
                if($(this).data('reporttype') == 'table'){
                  if (chartContainer.is(":visible")) {
                    if (!container3.dataTableDiv) {
                      container3.update({
                          exporting: {
                              showTable: true
                          }
                      });
                    } else {
                        $(container3.dataTableDiv).show();
                    }
                  }
                  chartContainer.hide();
                }else{
                  chartContainer.show();
                  container3.update({
                      exporting: {
                          showTable: false
                      }
                  });
                  $(container3.dataTableDiv).hide();
                }
              });
              $(document).on('graphsReload', '#segmented-button', function(){
                  var action = $("#segmented-button").data('action');
                  var timestamps = $("#segmented-button").data('timestamps');
                  var moduleid = $("#segmented-button").data('moduleid');
                  var courseid = $("#segmented-button").data('course');
                  return Str.get_strings([{
                      key: 'hitsviews',
                      component: 'stream'
                  }]).then(function(s) {
                  var promise = Ajax.call([{
                            methodname : 'mod_stream_completedUsersbyVideos',
                    args: {moduleid: moduleid, timestamps: timestamps}
                          }]);
                          promise[0].done(function(data){
                          var data = JSON.parse(data);
                              container3.update({
                                series: [{
                                  name: s[0],
                                  data: data,
                                  dataLabels: {
                                      enabled: true,
                                      rotation: -90,
                                      color: '#FFFFFF',
                                      align: 'right',
                                      format: '{point.y}', // one decimal
                                      y: 10, // 10 pixels down from the top
                                      style: {
                                        fontSize: '13px',
                                        fontFamily: 'Verdana, sans-serif'
                                      }
                                  }
                              }]
                              });
                          });
                        });
                  });
                }
            },
            }
        });
