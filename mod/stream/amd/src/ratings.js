/**
 * Add a create new group modal to the page.
 *
 * @module     mod/stream
 * @class      Ratings
 * @package    stream
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
define(['core/ajax', 'core/str','core/templates', 'jquery', 'jqueryui'],
        function(Ajax, Str, templates, $) {
    var ratePicker = function (target, options){
        var self = this;
        if (typeof options === 'undefined'){
            options = {};
        }
        options.max = typeof options.max === 'undefined' ? 5 : options.max;
        options.rgbOn = typeof options.rgbOn === 'undefined' ? "#f1c40f" : options.rgbOn;
        options.spaceWidth = typeof options.spaceWidth === 'undefined' ? '1px' : options.spaceWidth;
        options.fontsize = typeof options.fontsize === 'undefined' ? '25px' : options.fontsize;
        options.rgbOff = typeof options.rgbOff === 'undefined' ? "#ecf0f1" : options.rgbOff;
        options.rgbSelection = typeof options.rgbSelection === 'undefined' ? "#ffcf10" : options.rgbSelection;
        options.cursor = typeof options.cursor === 'undefined' ? "pointer" : options.cursor;
        options.indicator = typeof options.indicator === 'undefined' ? "fa fa-star" : "fa "+options.indicator;
        var stars = typeof $(target).data('stars') == 'undefined' ? 0 : $(target).data('stars');

        $(target).css('cursor', options.cursor);
        $(target).append($("<input>", {type : "hidden", name : target.replace("#", ""), value : stars}));

        $(target).append($("<i>", {class : options.indicator, style : "display:none; font-size: "+options.fontsize+"; color: transparent;"}));
        for (var i = 1; i <= options.max; i++){
            var icon = $("<i>", {class : options.indicator, style : "font-size: "+options.fontsize+"; margin-left: "+options.spaceWidth+"; color:" + (i <= stars ? options.rgbOn : options.rgbOff)});
            $(target).append(icon);

        }
        self.set_target_width(target, options);
        $(target).append($("<i>", {class : options.indicator, style : "display:none; font-size: "+options.fontsize+"; color: transparent;"}));
        $.each($(target + " > i"), function (index, item){
            $(item).click(function (){
                $("[name=" + target.replace("#", "") + "]").val(index);
                for (var i = 1; i <= options.max; i++){
                    $($(target + "> i")[i]).css("color", i <= index ? options.rgbOn : options.rgbOff);
                }
                if (!(options.rate === 'undefined')){
                    stars = index;
                    self.set_user_ratings(target, index);
                }
            });
            $(item).mouseover(function (){
                for (var i = 1; i <= options.max; i++){
                    $($(target + " > i")[i]).css("color", i <= index ? options.rgbSelection : options.rgbOff);
                }
            });
            $(item).mouseleave(function(){
                $("[name=" + target.replace("#", "") + "]").val(index);
                for (var i = 1; i <= options.max; i++){
                    $($(target + "> i")[i]).css("color", i <= stars ? options.rgbOn : options.rgbOff);
                }
            });
        });
    };
    ratePicker.prototype.set_target_width = function(target, options){
        var indicator = options.indicator.replace(/\ /g, '.');
        var spaceWidth = options.spaceWidth;
        var item_width = $('.'+indicator).width();
        $(target).width(((item_width+spaceWidth) * (options.max+1))+'px');
    };
    ratePicker.prototype.set_user_ratings = function(target, stars){
        var data = $(target).data();
        var promise = Ajax.call([{
            methodname: 'mod_stream_submit_rating',
            args: { componentid : data.itemid,  ratingvalue : stars }
        }]);
        promise[0].done(function(resp) {
            self.html(resp);
        });
    }
    return {
        init : function(target, options){
            options = JSON.parse(options);
            return new ratePicker(target, options);
            
        },
        updatevalues: function (params){
            var promise = Ajax.call([{
                        methodname: 'mod_stream_like_dislike',
                        args: params
                    }]);
                    promise[0].done(function(resp) {
                    if (params.likestatus == 2) {
                        $(".fa-thumbs-down").addClass('active');
                        $(".fa-thumbs-up").removeClass('active');
                    }else{
                        $(".fa-thumbs-up").addClass('active');
                        $(".fa-thumbs-down").removeClass('active');
                    }
                    $(".count_unlikearea_"+params.componentid).html(resp.dislike);
                    $(".count_likearea_"+params.componentid).html(resp.like);
                    $("#loading_image").hide();
                    });
        },
        trigger: function(){
            $(document).ready(function(){
                var content = $('#ratingList').html();
                if(content == ''){
                    $('#ratingList').html(' ');
                    var self = $('#ratingList');
                    var data = $('#ratingList').data();
                    var params = {};
                    params.itemid = data.itemid;
                    params.ratearea = data.ratearea;
                    params.contextid = 1;

                    var promise = Ajax.call([{
                        methodname: 'mod_stream_ratings_get_ratings_info',
                        args: params
                    }]);
                    promise[0].done(function(resp) {
                        templates.render('mod_stream/detailed_info_loaded', resp).then(function(html,js) {
                            self.html(html);
                        });
                    });
                }
            });
            $('.overall_users.mt-10').hover(function(){
                var content = $(this).find('.rating_tooltip').html();
                if(content == ''){
                    $(this).find('.rating_tooltip').html(' ');
                    var self = $(this).find('.rating_tooltip');
                    var data = $(this).find('.rating_tooltip').data();
                    var params = {};
                    params.itemid = data.itemid;
                    params.ratearea = data.ratearea;
                    params.contextid = 1;

                    var promise = Ajax.call([{
                        methodname: 'mod_stream_ratings_get_ratings_info',
                        args: params
                    }]);
                    promise[0].done(function(resp) {
                        templates.render('mod_stream/detailed_info', resp).then(function(html,js) {
                            self.html(html);
                        });
                    });
                }
            });
        },
        load: function(){

        }
    }
});