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
        'core/ajax',
        'mod_stream/videojs-playbackrate-adjuster',
        'mod_stream/videojs-contrib-quality-levels',
        'mod_stream/videojs-hls-quality-selector'], 
        function($,videojs, Ajax){
            return {
                load: function(args){
                    var values = JSON.parse(args);
                    const player = videojs(values.identifier);
                    var myVideoPlayer = document.getElementById(values.identifier);
            
                    player.src({
                        src: values.src,
                        type: 'application/x-mpegURL'
                    });

                     player.hlsQualitySelector({
                       displayCurrentQuality: true,
                    });

                     player.on('loadedmetadata', function() {
                         $('#stream_duration').val(player.duration().toFixed(0));
                     });
                    

                    $(window).bind('beforeunload', function(){
                        var currenttime = player.currentTime();
                        var lengthOfVideo = player.duration();

                        var promises = Ajax.call([{
                            methodname: 'mod_streamattempts',
                            args: { moduleid:values.cm, courseid:values.course, duration:lengthOfVideo, currenttime: currenttime, event:'pause'},
                        }])

                        promises[0].done(function(response) {
                            console.log('Before unload Details updated');
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
                        console.log('completed duration is 0');
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
                            console.log('Not inserted');
                        });
                    });

                    player.on("pause", function() {
                        var currenttime = player.currentTime();
                        var lengthOfVideo = player.duration();

                            var promises = Ajax.call([{
                                methodname: 'mod_streamattempts',
                                args: { moduleid:values.cm, courseid:values.course, duration:lengthOfVideo, currenttime: currenttime, event:'pause'},
                            }])

                            promises[0].done(function(response) {
                                console.log('Pauselog Details updated');
                            }).fail(function(ex) {
                                console.log('Not Updated' + JSON.stringify(ex));
                            });
                    });
            }
        }
    });
