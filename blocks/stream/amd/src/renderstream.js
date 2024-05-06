define(['jquery','media_videojs/video-lazy', 'core/modal_events', 'core/modal_factory','core/templates'], function($, videojs, ModalEvents, ModalFactory,Templates) {
    return {
        init: function(cmid) {
          $(document).on('click', '.renderervideo', function(){
            var data = $(this).data();
            ModalFactory.create({
                title: data.title,
                type: ModalFactory.types.DEFAULT,
                body: Templates.render('block_stream/preview', data),
            }).done(function(modal) {
                modal.show();
                modal.getRoot().on(ModalEvents.shown, function(){
                    const player = videojs('rendervideo_'+data.id);
                    var vid = player.src({
                        autoplay:true,
                        controls:true,
                        src: data.streamurl,
                        type: 'application/x-mpegURL'
                    });        
                    player.play();
                    });
                modal.getRoot().on(ModalEvents.hidden, function(){
                    var currentVideo = videojs('rendervideo_'+data.id);
                    currentVideo.dispose();
                });
            }.bind(this));
          });

        }
    }
});
