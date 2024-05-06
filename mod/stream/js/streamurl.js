M.stream_url = {};

M.stream_url.init = function(Y, options) {
    options.formcallback = M.stream_url.callback;
    if(typeof(options.client_id) == 'undefined'){
        alert("Please enable stream repository");
    }
    if (!M.core_filepicker.instances[options.client_id]) {
        M.core_filepicker.init(Y, options);
    }
    Y.on('click', function(e, client_id) {
        e.preventDefault();
        M.core_filepicker.instances[client_id].show();
    }, '#filepicker-button-js-'+options.client_id, null, options.client_id);
    
};

M.stream_url.callback = function (params) {
    require(['media_videojs/video-lazy', 'jquery'], function(videojs, $){
        videoparams = params.url.split('/');
        var videoidIndex = videoparams.length-2;
        $('#stream_external_url').val(params.url);
        $('#stream_external_videoid').val(videoparams[videoidIndex]);
        $('.stream_file_selector').show();
        const player = videojs('mod_stream_form_video');
        player.src({
            autoplay:true,
            src: params.url,
            type: 'application/x-mpegURL'
        });
       player.on('loadedmetadata', function() {
         $('#stream_duration').val(player.duration().toFixed(0));
        });
    });
};
