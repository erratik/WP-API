var $ = jQuery;
var _tempData ;
var editing_video_ids;

var bulk_action;
var action_dataset;
var video_batches;


jQuery(function($) {

    var video_idx = 0;
    var adding_video_count = 0;

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

            // use the first batch to start
            video_batches = _.chunk(editing_video_ids, 10);
            action_dataset.ids = video_batches[0];

            // recursive function to go through the batches
            editVideoLoop(action_dataset, video_batches, loopIt);


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

function loopIt() {
    video_batches = _.drop(video_batches);
    action_dataset.ids = video_batches[0];
    if (video_batches.length) {

        editVideoLoop(action_dataset, video_batches, loopIt);
    } else {

        $('.wpvr_manage_refresh:eq(0)').click();
        $('.cutv_bulk_apply').attr('style', 'display: none !important;');
        $('.wpvr_manage_bulk_actions_select').val('');

        console.groupEnd();
    }
}

function editVideoLoop(action_dataset, batch, cb) {

    if (bulk_action != 'publish')
        action_dataset.ids = editing_video_ids;

    // changing the video status
    _cutv.ajax(ajaxurl, action_dataset).then(function (action_result) {

        console.groupCollapsed('result from '+action_dataset.action);
        console.info('[admin.js] edited '+ action_dataset.ids.length +' video(s): ');
        // console.log(action_dataset.ids);

        console.groupCollapsed('full response: '+action_dataset.action);
        console.debug(action_result);
        console.groupEnd();

        var result = JSON.parse(action_result.split('data:')[1]);

        if (result.length) {
            // console.info('result typeof '+ typeof result);
            console.debug(result);
            console.log('got videos that don\'t exist');
            console.groupEnd();

            if (bulk_action != 'publish') {

                console.log('got videos that exist, need to delete them');
                _cutv.ajax(ajaxurl, {
                    action: 'cutv_clear_snaptube_video',
                    videos: result,
                    status: bulk_action
                }).then(function (data) {
                    console.log('[finish bulk action after clearing]', data);
                    finishBulkAction('cutv_clear_snaptube_video', data);
                });
            } else {
                editSnaptubeVideos(bulk_action, batch[0], cb);
            }


        } else {
            console.log('got videos that exist, videos have been updated, nothing more to do?');

            // editSnaptubeVideos(bulk_action, batch[0], cb);
            loopIt();
        }
        $('.wpvr_manage_refresh:eq(0)').click();


    });
}

function getSelectedVideos() {

    var editing_video_ids = [];
    $('.wpvr_video.checked').each(function () {
        var video_id = $(this).prop('id');
        var id_str = video_id.replace('video_', '');
        editing_video_ids.push(Number(id_str));
    });

    return editing_video_ids;
}

function editSnaptubeVideos(wp_action, videos, cb) {
    var vids = [];
    if (wp_action == 'publish') {

        // get all the posts we are editing (only published videos are sent here)
        var GetVideos = $.Deferred();

        $.ajax({
            url: wpApiSettings.root + 'cutv/v2/videos/?include='+_.join(videos, ','),
            success: function (response) {
                // console.log(videos);
                vids = [];
                _.forEach(videos, function (video_id, n) {

                    var video = _.find(response[n], {'id': video_id});
                    // console.log(video);

                    var $video_wrapper = $('#video_' + video.id);
                    var image = $video_wrapper.find('.wpvr_video_thumb img').attr('src');
                    var file_link = 'http://www.youtube.com/watch?v=' + $video_wrapper.find('[post_id="' + video.id + '"]').attr('video_id');
                    var description = video.content.rendered;

                    vids.push({
                        categories: video.categories,
                        description: description.replace(/<\/?[^>]+(>|$)/g, ""),
                        name: $video_wrapper.find('.wpvr_video_title').text(),
                        file: file_link,
                        slug: video.slug,
                        tags: video.tags,
                        duration: $video_wrapper.find('.wpvr_video_duration').text(),
                        image: image,
                        opimage: image.replace('-200x150', ''),
                        post_date: moment(video.date).format('YYYY-MM-DD HH:mm:ss'),
                        link: file_link,
                        member_id: video.author,
                        id: video.id
                    });
                });

                vids = _.compact(vids);

                _cutv.ajax(ajaxurl, {
                    action: 'cutv_convert_snaptube',
                    videos: vids
                }).then(function (data) {
                    finishBulkAction('cutv_convert_snaptube', data);
                    cb();
                });

            }
        });
    } else {

    }
}

function finishBulkAction(action, response) {

    $('.wpvr_manage_refresh:eq(0)').click();
    $('.cutv_bulk_apply').attr('style', 'display: none !important;');
    $('.wpvr_manage_bulk_actions_select').val('');

    // console.groupCollapsed('result from '+action);

    console.groupCollapsed('full response: ' + action);
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

