var $ = jQuery;
var _tempData ;
var editing_video_ids;

jQuery(function($) {

    var bulk_action;
    var video_idx = 0;
    var adding_video_count = 0;
    var action_dataset;

    var button_html = '<div class="cutv_bulk_apply pull-left">'
        + '<button class="ui labeled icon button"><i class="add circle icon"></i>Apply to  <span class="wpvr_count_checked"></span> videos</button></div>';
    $('.wpvr_manage_bulk_actions').append(button_html);

    $('body')
        .on('change', '.wpvr_manage_bulk_actions_select', function() {

            // SET THE CHOSEN BULK ACTION
            bulk_action = $(this).val();

            // SHOW THE RIGHT BUTTON IF WE ARE GOING TO DO ACTIONS THAT CHANGE THE VIDEOS IN SNAPTUBE
            switch(bulk_action) {
                case 'publish':
                case 'pending':
                case 'untrash':
                case 'trash':
                case 'draft':
                    action_dataset = {
                        action: 'cutv_set_wpvr_videos_status',
                        ids: getSelectedVideos(),
                        status: bulk_action
                    };

                    // todo: this should be a function
                    $('.wpvr_manage_bulkApply').attr('style', 'display: none !important;');
                    $('.cutv_bulk_apply').attr('style', 'display: block !important;');
                    break;
                default:
                    console.log('default action chosen: ', bulk_action);
                    $('.wpvr_manage_bulkApply').attr('style', 'display: block !important;');
                    $('.cutv_bulk_apply').attr('style', 'display: none !important;');
            }

        })
        // APPLY BULK WPVR VIDEO UPDATES
        .on('click', '.cutv_bulk_apply', function(e) {
            e.preventDefault();

            // find the IDs that are getting edited
            editing_video_ids = getSelectedVideos();


            _cutv.ajax(ajaxurl, action_dataset).then(function (action_result) {

                console.group('result from '+action_dataset.action);
                console.info('[admin.js] edited '+ editing_video_ids.length +' video(s): ');
                console.log(editing_video_ids);

                console.group('full response: '+action_dataset.action);
                console.debug(action_result);
                console.groupEnd();

                var result = JSON.parse(action_result.split('data:')[1]);
                console.info('result typeof '+ typeof result);
                console.debug(result);
                console.groupEnd();


                if (result.length) {
                    console.log('got videos that exist');
                    editSnaptubeVideos(bulk_action, result);

                } else {
                    console.log('got videos that don\'t exist');
                    editSnaptubeVideos(bulk_action);
                }
                $('.wpvr_manage_refresh:eq(0)').click();


            });
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
    var _tempData;

    // console.log( "Triggered ajaxSuccess handler. The Ajax response was: " + xhr.responseText );

    if ( url.search('bulk_single_action') > 0 ) {

        console.log( "URL: " + url );
        _tempData = xhr.responseText;

    }

    jQuery('.cutv_bulk_apply, .wpvr_manage_bulkApply').attr('style', 'display: none !important;');
    jQuery('.wpvr_manage_bulk_actions_select').val('').hide();
});



function getSelectedVideos() {

    var editing_video_ids = [];
    $('.wpvr_video.checked').each(function () {
        var video_id = $(this).prop('id');
        var id_str = video_id.replace('video_', '');
        editing_video_ids.push(Number(id_str));
    });

    return editing_video_ids;
}

function editSnaptubeVideos(wp_action, videos) {
    var vids = [];
    if (wp_action == 'publish') {

        // get all the posts we are editing (only published videos are sent here)
        var GetVideos = $.Deferred();
        $.ajax({
            url: wpApiSettings.root + 'cutv/v2/videos/',
            success: function (response) {

                vids = _.map(response, function (v) {
                    v = v.data;
                    var save_post = _.findIndex(editing_video_ids, function (o) { return o == v.id; }) != -1 ? true : false;
                    if (save_post) {

                        console.log(v);

                        var $video_wrapper = $('#video_' + v.id);
                        var image = $video_wrapper.find('.wpvr_video_thumb img').attr('src');
                        var file_link = 'http://www.youtube.com/watch?v=' + $video_wrapper.find('[post_id="' + v.id + '"]').attr('video_id');
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
                            categories: v.categories,
                            description: description.replace(/<\/?[^>]+(>|$)/g, ""),
                            name: $video_wrapper.find('.wpvr_video_title').text(),
                            file: file_link,
                            slug: v.slug,
                            tags: v.tags,
                            duration: $video_wrapper.find('.wpvr_video_duration').text(),
                            image: image,
                            opimage: image.replace('-200x150', ''),
                            post_date: moment(v.date).format('YYYY-MM-DD HH:mm:ss'),
                            link: file_link,
                            member_id: v.author,
                            id: v.id
                        }
                    }
                });
                vids = _.compact(vids);
                _cutv.ajax(ajaxurl, {
                    action: 'cutv_convert_snaptube',
                    videos: vids
                }).then(function (data) {
                    finishBulkAction('cutv_convert_snaptube', data);
                });
            }
        });
    } else {

        _cutv.ajax(ajaxurl, {
            action: 'cutv_clear_snaptube_video',
            videos: videos,
            status: wp_action
        }).then(function (data) {
            finishBulkAction('cutv_clear_snaptube_video', data);
        });
    }
}

function finishBulkAction(action, response) {

    $('.wpvr_manage_refresh:eq(0)').click();
    $('.cutv_bulk_apply').attr('style', 'display: none !important;');
    $('.wpvr_manage_bulk_actions_select').val('');

    // console.group('result from '+action);

    console.group('full response: ' + action);
    console.debug(response);
    console.groupEnd();

    // var result = JSON.parse(response.split('data:')[1]);
    // console.info('result typeof '+ typeof result);
    // console.debug(result);
    // console.groupEnd();
}

// UTILS
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
        url: options.url,
        data: options.data,
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

