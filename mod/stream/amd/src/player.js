/**
 * Streaming video js
 *
 * @module     mod_stream/stream
 * @class      stream
 * @package    mod_stream
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
define(['jquery',
        'core/str',
        'media_videojs/video-lazy',
        'core/ajax',
        'mod_stream/videojs-playbackrate-adjuster',
        'mod_stream/videojs-contrib-quality-levels',
        'mod_stream/videojs-hls-quality-selector'],
        function($, Str,videojs, Ajax){
            return {
                load: function(args){
                    var values = JSON.parse(args);
                    const player = videojs(values.identifier);
                    var myVideoPlayer = document.getElementById('mod_stream_form_video');
                    player.src({
                        src: values.src,
                        type: 'application/x-mpegURL'
                    });
                     player.hlsQualitySelector({
                       displayCurrentQuality: true,
                    });
                    if(typeof(myVideoPlayer) != 'undefined'  && myVideoPlayer != null){
                        myVideoPlayer.onloadedmetadata = function() {
                          console.log('metadata loaded!');
                          console.log(this.duration);//this refers to myVideo
                        };
                        myVideoPlayer.addEventListener('loadedmetadata', function () {
                            console.log(myVideoPlayer.duration);
                            $('#stream_duration').val(myVideoPlayer.duration.toFixed(0));
                        });
                    }

                 /*   $(window).bind('beforeunload', function(){
                        var currenttime = player.currentTime();
                        var lengthOfVideo = player.duration();
                        var promises = Ajax.call([{
                            methodname: 'mod_streamattempts',
                            args: { moduleid:values.cm, courseid:values.course, duration:lengthOfVideo, currenttime: currenttime, event:'pause'},
                        }])

                        promises[0].done(function(response) {
                            console.log(Str.get_string('unloaddetails','block_stream'));
                        }).fail(function(ex) {
                            console.log('Not Updated' + JSON.stringify(ex));
                        });
                    });

                    var promises = Ajax.call([{
                        methodname: 'mod_streamattempts',
                        args: { moduleid:values.cm, courseid:values.course, event: 'paused'},
                    }])

                    promises[0].done(function(response) {
                        var pause = JSON.stringify(response);
                        var check = JSON.parse(pause);
                        if(check.recordid != '1'){
                            player.currentTime(check.recordid);
                        }else {
                            player.currentTime(0);
                        }
                        player.on("seeking", function(event) {
                            if (currentTime < player.currentTime()) {
                                player.currentTime(currentTime);
                            }
                        });

                        player.on("seeked", function(event) {
                            if (currentTime < player.currentTime()) {
                                player.currentTime(currentTime);
                            }
                        });
                        setInterval(function() {
                            if (!player.paused()) {
                                currentTime = player.currentTime();
                            }
                        }, 1000);
                    }).fail(function(ex) {
                        console.log(Str.get_string('completedduration','block_stream'));
                    });

                    player.on("play", function() {
                        var lengthOfVideo = player.duration();
                        var promises = Ajax.call([{
                            methodname: 'mod_streamattempts',
                            args: { moduleid:values.cm, courseid:values.course, duration:lengthOfVideo, event:'play'},
                        }])

                        promises[0].done(function(response) {
                            console.log('Inserted');
                        }).fail(function(ex) {
                            console.log(Str.get_string('notintrested','block_stream'));
                        });
                    });*/

                    player.on("pause", function() {
                        var currenttime = player.currentTime();
                        var lengthOfVideo = player.duration();

                            var promises = Ajax.call([{
                                methodname: 'mod_streamattempts',
                                args: { moduleid:values.cm, courseid:values.course, duration:lengthOfVideo, currenttime: currenttime, event:'pause'},
                            }])

                            promises[0].done(function(response) {
                                console.log(Str.get_string('Pauselog','block_stream'));
                            }).fail(function(ex) {
                                console.log(Str.get_string('notupdated','block_stream') + JSON.stringify(ex));
                            });
                    });
            }
        }
    });
