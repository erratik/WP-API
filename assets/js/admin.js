var $ = jQuery;
var bulk_action;
var _tempData ;

$(document).ready(function() {


    $('body')

        .one('mouseover', '.wpvr_video', function(e) {
            $('.wpvr_manage_main_form').before($('.wpvr_manage_bulkApply').clone());
            $('.wpvr_manage_bulkApply:eq(0)').addClass('cutv_bulk_apply ui red button');

        })
        .on('click', '.cutv_bulk_apply', function(e) {
            e.preventDefault();
            // console.log('save the posts');
        })

        // CREATE A WPVR CATEGORY
        .on('click', '#add-category-button', function(){
            var channelName = $('[name="cutv-new-channel-name"]').val();
            var slug = channelName.toLowerCase();
            slug = slug.replace(/ /g, '-');

            _cutv.ajax(wpApiSettings.root + 'cutv/v2/categories', {
                    description: 'Mlkshk flannel deep v marfa hashtag brooklyn.',
                    name: channelName,
                    slug: slug
                },
                {method: 'POST'}
            ).then(function (data) {
                console.log(data);
                _cutv.ajax(ajaxurl, {
                        'action' : 'cutv_add_channel',
                        channelName: data.name,
                        slug: data.slug,
                        description: data.description,
                        cat_id: data.id
                    }
                ).then(function (data) {
                    // console.log(data);
                });
            });

        });

}).ajaxSuccess(function( event, xhr, settings ) {
    var url = settings.url;

    //console.log( "Triggered ajaxSuccess handler. The Ajax response was: " + xhr.responseText );


    if ( url.search('bulk_single_action') > 0  && _tempData != xhr.responseText) {
        // console.log(url.search('bulk_single_action') );
        //find out what action we are doing on the video(s)
        bulk_action = $('.wpvr_manage_bulk_actions_select').val();

        // find the IDs that are getting edited
        var editing_video_ids = [];
        $('.wpvr_video.checked').each(function () {
            editing_video_ids.push($(this).find('.wpvr_video_cb').val());
        });
        console.log('action =>' , bulk_action);

        // get all the posts we are editing
        var GetVideos = $.Deferred();
        $.ajax({
            url: wpApiSettings.root + 'cutv/v2/videos',
            success: function( response ) {
                GetVideos.resolve(response.data);
            }
        });

        GetVideos.then(function (data) {
            console.log('got videos: ', data.length);
            var vids = _.map(data, function (v) {
                //console.log(v);
                var $video_wrapper = $('#video_'+v.id);
                var image = $video_wrapper.find('.wpvr_video_thumb img').attr('src');
                var file_link = 'http://www.youtube.com/watch?v='+$video_wrapper.find('[post_id="'+v.id+'"]').attr('video_id');
                //
                // console.log('duration', $video_wrapper.find('.wpvr_video_duration').text());
                // console.log('image', image);
                // console.log('opimage', image.replace('-200x150', ''));
                // console.log('file', file_link);
                // console.log('link', file_link);
                // console.log('post_date', moment(v.date).format('YYYY-MM-DD HH:mm:ss'));
                //
                var description = v.content.rendered;

                return {
                    cat_id : v.categories,
                    description : description.replace(/<\/?[^>]+(>|$)/g, ""),
                    name : $video_wrapper.find('.wpvr_video_title').text(),
                    file : file_link,
                    slug : v.slug,
                    duration : $video_wrapper.find('.wpvr_video_duration').text(),
                    image : image,
                    opimage : image.replace('-200x150', ''),
                    post_date : moment(v.date).format('YYYY-MM-DD HH:mm:ss'),
                    link : file_link,
                    member_id: v.author
                }
            });
            console.log(vids);

            _cutv.ajax(ajaxurl, {
                    action : 'cutv_convert_snaptube',
                    videos : vids
            }).then(function (data) {
                // console.log(data);
            });

        });
        // set temp data to know if i need to run the Get again
        _tempData = xhr.responseText;
    }
});


var _cutv = {
    ajax : function(url, data, options) {
        return new MakePromise({ url: url, data: data, options: options });
    }
};
function MakePromise(options){

    //log({msg: "A promise is being made...", color: 'purple' });

    var params = {
        method: 'POST',
        cache: true,
        showErrors: true,
        success: function(result) {
            //log({msg:"Promise went through!", color: 'purple' });
            //console.groupEnd();
            promise.resolve(result);
        },
        error: function(jqXHR, textStatus, error) {

            if ( jqXHR.status == 400 ) {
                errorMessage = jqXHR.responseText;
                log({msg: "%c(╯°□°）╯ should be accompanied by custom message to display", color: 'red' });
                log({msg: errorMessage , color: 'red' });


            } else {
                log({msg: "%c(╯°□°）╯", color: 'red' });
                errorMessage = { error: jqXHR.message, statusCode: jqXHR.code};
            }

            promise.reject(errorMessage);
        }
    };

    $.extend(params, options.options);

    var promise = $.Deferred();
    $.ajax({
        type: params.method,
        url: params.url,
        data: params.data,
        success: params.success,
        error: params.error,
        beforeSend: function ( xhr ) {
            xhr.setRequestHeader( 'X-WP-Nonce', wpApiSettings.nonce );
        }
    });
    // console.log(promise)
    return promise;
}

DEBUG_LEVEL = window.location.hostname == 'cutv.dev' ? 3 : 0;
if (navigator.appName == "Microsoft Internet Explorer") DEBUG_LEVEL = 0;
function log(options) {

    var defaults = {
        msg: null,
        level: DEBUG_LEVEL,
        group: false,
        color: 'blue'
    };
    $.extend(defaults, options);

    if ( DEBUG_LEVEL > 2 && navigator.appName != "Microsoft Internet Explorer") {
        console.log("%c" + options.msg, "color:"+options.color+";");
    }
}

