<?php


function cutv_add_channel()
{

    // The $_REQUEST contains all the data sent via ajax
    if (isset($_REQUEST)) {
        global $wpdb;

        $cat_id = wp_insert_category(
            array(
                'cat_name' => $_REQUEST['channelName'],
                'category_description' => '',
                'category_nicename' => $_REQUEST['slug'],
                'category_parent' => ''
            )
        );

        // Set Channel Status
        if ( ! add_term_meta( $cat_id, 'cutv_channel_enabled', $_REQUEST['enabled'], true)) {
            update_term_meta($cat_id, 'cutv_channel_enabled', $_REQUEST['enabled']);
        }

        $playlists = $wpdb->get_results( 'SELECT * FROM ' . SNAPTUBE_PLAYLISTS );

        $query = $wpdb->prepare("INSERT INTO " . SNAPTUBE_PLAYLISTS . " (pid, playlist_name, playlist_slugname, playlist_desc, is_publish, playlist_order) VALUES ( %d, %s, %s, %s, %d, %d )",
            array($cat_id, $_REQUEST['channelName'], $_REQUEST['slug'], '', 1, count($playlists))
        );
        $wpdb->query($query);

    }

    // Always die in functions echoing ajax content
    die();
}
add_action('wp_ajax_cutv_add_channel', 'cutv_add_channel');

function cutv_remove_channel()
{

    // The $_REQUEST contains all the data sent via ajax
    if (isset($_REQUEST)) {
        global $wpdb;

        $cat_id =  $_REQUEST['id'];

        wp_delete_category($cat_id);
        $wpdb->delete( $wpdb->termmeta, array( 'term_id' => $cat_id ) );
        $wpdb->delete( SNAPTUBE_PLAYLISTS, array( 'pid' => $cat_id ) );
        $wpdb->delete( SNAPTUBE_PLAYLIST_RELATIONS, array( 'playlist_id' => $cat_id ) );

        header("HTTP/1.1 200 Ok");

    }

    // Always die in functions echoing ajax content
    die();
}
add_action('wp_ajax_cutv_remove_channel', 'cutv_remove_channel');

function cutv_set_wpvr_videos_status()
{

    // The $_REQUEST contains all the data sent via ajax
    if (isset($_REQUEST)) {
        global $wpdb;

        $videos_to_convert = array();

        $status = false;
        switch($_REQUEST['status']) {
            case 'pending':
            case 'untrash':
            case 'draft':
                $status = 'pending';
                break;
            default:
                $status = $_REQUEST['status'];

        }
        if ($status != false) {
            foreach ($_REQUEST['ids'] as $wpvr_video_id) {

                $post_id = wp_update_post(array(
                    'ID' => $wpvr_video_id,
                    'post_status' => $status
                ));
                cutv_log(3, '[wp_posts][wpvr video #'.$post_id.'] is now => '.$status);

                // find out if the video already exists as a snaptube video
                $wpvr_video = get_post($wpvr_video_id);


                $snaptube_post = $wpdb->get_row( "SELECT * FROM $wpdb->posts WHERE post_name = '".$wpvr_video->post_name."' AND post_type='videogallery'");

                $videos_to_convert = [];

                if ($snaptube_post != null) {

                    cutv_log(4, '[wp_posts] found wpvr video, #'.$snaptube_post->ID . ', '.$snaptube_post->post_title, "\n", "\$wpvr_video_id: $wpvr_video_id ");
//                    print_r(cutv_get_snaptube_video($snaptube_post->ID));
//                    echo  "\n" . PHP_EOL;
                    if ($status == 'publish') {
                        // UPDATE THE TAGS AND CATEGORIES
                        cutv_make_snaptube_cats($wpvr_video_id, cutv_get_snaptube_video($snaptube_post->ID));
                        cutv_make_snaptube_tags(get_the_tags($wpvr_video_id), $wpvr_video_id);

                        cutv_log(4, "cutv_get_snaptube_id(", $wpvr_video_id, "), get_the_tags(", $wpvr_video_id, ")");

                    } else {
                        $videos_to_convert[] = $post_id;
                    }

                } else {
                    cutv_log(4, '[wp_posts] no snaptube video corresponding to this wpvr video');
                    $videos_to_convert[] = $post_id;

                }

            }
            echo "data:".json_encode($videos_to_convert);

        }


    }

    // Always die in functions echoing ajax content
    die();
}
add_action('wp_ajax_cutv_set_wpvr_videos_status', 'cutv_set_wpvr_videos_status');


function cutv_toggle_channel() {

    // The $_REQUEST contains all the data sent via ajax
    if (isset($_REQUEST)) {
        global $wpdb;

        $channel_id = $_REQUEST['channel'];
        $sources = $_REQUEST['sources'];

        if ( ! add_term_meta( $channel_id, 'cutv_channel_enabled', $_REQUEST['enabled'], true)) {
            update_term_meta($channel_id, 'cutv_channel_enabled', $_REQUEST['enabled']);
        }

        if ( ! add_term_meta( $channel_id, 'cutv_channel_sources', $sources, true)) {
            update_term_meta($channel_id, 'cutv_channel_sources', $sources);
        }

        $res = array(
            json_decode(get_term_meta( $channel_id, 'cutv_channel_enabled', true )),
            json_decode(get_term_meta( $channel_id, 'cutv_channel_sources', true ))
        );

        header('Content-Type: application/json');
        echo json_encode($res);
    }

    // Always die in functions echoing ajax content
    die();

}
add_action('wp_ajax_cutv_toggle_channel', 'cutv_toggle_channel');

function cutv_cleanup_channel() {

    die();
}
add_action('wp_ajax_cutv_cleanup_channel', 'cutv_cleanup_channel');

function cutv_get_channels() {
    global $wpdb;
    $channels_rows = $wpdb->get_results("SELECT * FROM " . SNAPTUBE_PLAYLISTS ." WHERE pid > 1" );

    $channels = [];
    foreach ($channels_rows as $channel) {
        $channel->cutv_channel_img = get_term_meta( $channel->pid, 'cutv_channel_img', true );
        $channel->enabled = get_term_meta( $channel->pid, 'cutv_channel_enabled', true );

        $channels[] = $channel;
    };


    if (isset($_REQUEST) && isset($_REQUEST['json'])) {
//        header('Content-Type: application/json');
        echo json_encode($channels);
    } else {
        return $channels;
    }

    die();

}
add_action('wp_ajax_nopriv_cutv_get_channels', 'cutv_get_channels');
add_action('wp_ajax_cutv_get_channels', 'cutv_get_channels');

function get_the_catalog_cat( $id = false ) {
    $categories = get_the_terms( $id, 'catablog-terms' );
    if ( ! $categories || is_wp_error( $categories ) )
        $categories = array();

    $categories = array_values( $categories );

    foreach ( array_keys( $categories ) as $key ) {
        _make_cat_compat( $categories[$key] );
    }

    /**
     * Filters the array of categories to return for a post.
     *
     * @since 3.1.0
     * @since 4.4.0 Added `$id` parameter.
     *
     * @param array $categories An array of categories to return for the post.
     * @param int   $id         ID of the post.
     */
    return apply_filters( 'get_the_categories', $categories, $id );
}


function cutv_get_snaptube_post_data($video_post, $wpvr_id) {
    $video_post->snaptube_vid = intval(cutv_get_snaptube_vid($wpvr_id));
    $video_post->snaptube_id = intval(cutv_get_snaptube_post_id($wpvr_id));
    $video_post->source_id = intval(get_post_meta( $wpvr_id, 'wpvr_video_sourceId', true ));
    // $video_post->snaptube_link_id = get_post_meta($wpvr_id, '_cutv_snaptube_video', true);
    $video_post->youtube_thumbnail = get_post_meta($wpvr_id, 'wpvr_video_service_thumb', true );
    $video_post->video_duration = convert_youtube_duration(get_post_meta($wpvr_id, 'wpvr_video_duration', true));

    if ($video_post->snaptube_vid == ! null) {
        $video_post->post_status = 'publish';
    }
    return $video_post;
}
