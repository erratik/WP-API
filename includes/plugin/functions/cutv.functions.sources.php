<?php

function cutv_update_source_categories() {

    // The $_REQUEST contains all the data sent via ajax
    if (isset($_REQUEST)) {
        global $wpdb;

        $channel_id = $_REQUEST['channel'];

        $sources =  json_decode($_REQUEST['sources']);

        $sourceObj = [];
        // if there is metadata matching the channel and the post_id is not in this source list, delete it
        $wpdb->delete( $wpdb->postmeta, array( 'meta_key' => 'wpvr_source_postCats_', 'meta_value' => $channel_id) );

        $wpdb->delete(
            SNAPTUBE_PLAYLIST_RELATIONS,
            array('playlist_id' => $channel_id )
        );

        foreach ($sources as $i => $source_id) {

            // add/update the channels for this source
            add_post_meta( $source_id, 'wpvr_source_postCats_', $channel_id, true);
            // if ( ! add_term_meta( $source_id, 'wpvr_source_postCats_', $channel_id, true)) {
            //     update_term_meta($source_id, 'wpvr_source_postCats_', $channel_id, true);
            // }

            $args = array(
                'numberposts' => -1,
                'post_type'   => 'wpvr_video',
                'post_status' => 'any',
                'meta_query' => array(
                    array(
                        'key'   => 'wpvr_video_sourceId',
                        'value' => $source_id,
                    )
                )
            );

            $source_videos =  cutv_get_snaptube_posts($args);


            foreach ($source_videos as $video) {
                cutv_make_snaptube_cats($video->ID, $video->snaptube_vid);
            }

            $sourceObj[] = $source_id;

        }


        echo implode(',', $sourceObj);


    }

    // Always die in functions echoing ajax content
    die();
}
add_action('wp_ajax_cutv_update_source_categories', 'cutv_update_source_categories');


// sources
function cutv_get_sources_info($channel_id) {

        if (isset($_REQUEST)) {
            $channel_id = $_REQUEST['channel_id'];
        }
        global $wpdb;

        $args = array(
            'post_type' => 'wpvr_source',
            'posts_per_page' => -1,
            'post_status' => 'publish'
        );


        // build source object
        $sourceObj = [];
        $sources = get_posts( $args );

        foreach ($sources as $k => $source) {


            $source_category = get_post_meta($source->ID, 'wpvr_source_postCats_', true);

            $source_name = get_post_meta($source->ID, 'wpvr_video_sourceName', true);
            if ($source_name == null) {
                $source_name = get_post_meta($source->ID, 'wpvr_source_name', true);
            }

            if ($source_category == $channel_id) {
                $sourceObj[] = (object) [
                    'name' => $source_name,
                    'ID' => $source->ID,
                    // 'videos' => cutv_get_source_videos($source->ID),
                    'selected' => true
                ];
            } elseif($source->post_type == 'wpvr_source') {

                $source_channel = $wpdb->get_row("SELECT * FROM wp_hdflvvideoshare_playlist WHERE pid = '$source_category'");
                $sourceObj[] = (object) [
                    'name' => $source_name,
                    'ID' => $source->ID,
                    'selected' => false,
                    'in_channel' => $source_channel->playlist_name,
                ];
            }


        }

        echo json_encode($sourceObj);

    // Always die in functions echoing ajax content
    die();
}
add_action('wp_ajax_cutv_get_sources_info', 'cutv_get_sources_info');


function get_meta_values( $key, $value) {

    global $wpdb;


    $r = $wpdb->get_results("SELECT post_id FROM wp_postmeta WHERE meta_key = '$key' AND meta_value = $value") ;

    foreach ($r as $meta_row) {
        $post_ids[] = $meta_row->post_id;
    }
    return $post_ids;
}

function cutv_get_sources_by_channel($channel_id) {
    $abridged = true;
    // The $_REQUEST contains all the data sent via ajax
    if (isset($_REQUEST)) {
        $channel_id = $_REQUEST['channel_id'];
        $abridged = false;
    }

    $source_ids = get_meta_values( 'wpvr_source_postCats_', $channel_id );


    if ($abridged == false) {

        $sources = [];
        foreach ($source_ids as $source_id) {

            $args = array(
                'numberposts' => -1,
                'post_type'   => 'wpvr_video',
                'post_status' => 'any',
                'meta_query' => array(
                    array(
                        'key'   => 'wpvr_video_sourceId',
                        'value' => $source_id,
                    )
                )
            );

            $posts =  cutv_get_snaptube_posts($args);

            $source_videos = new stdClass;
            $source_videos->unpublished = [];
            $source_videos->published = [];
            $source_videos->pending = [];

            // cutv_log(3, $posts, true);


            foreach ($posts as $post) {
                // cutv_log(3, 'vid: '. $post->snaptube_vid . ' => '. get_post_meta($post->ID, '_cutv_snaptube_video', true) . ' (' . $post->post_status.') ' . $post->post_title, true);

                if ($post->post_status == 'pending') { // pending

                    $source_videos->unpublished[] = $post->ID;
                } elseif ($post->post_status == 'draft') { // draft
                    $source_videos->pending[] = $post->ID;
                } else { // publish
                    $source_videos->published[] = $post->ID;
                }
            }



            $sources[] = (object) [
                'source_id' => $source_id,
                'source_video_counts' => $source_videos,
                'source_name' => get_post_meta( $source_id, 'wpvr_source_name', true )
            ];
        }

        echo json_encode($sources);

    } else {

        return $source_ids;

    }

    die();

}
add_action('wp_ajax_cutv_get_sources_by_channel', 'cutv_get_sources_by_channel');



	// wpvr functions
function cutv_get_source_video_posts($source_id) {

	global $wpdb;

    if (isset($_REQUEST)) {
        $source_id = $_REQUEST['source_id'];
        $args = array(
            'numberposts' => -1,
            'post_type'   => 'wpvr_video',
            'post_status' => 'any',
            'meta_query' => array(
                array(
                    'key'   => 'wpvr_video_sourceId',
                    'value' => $source_id,
                )
            )
        );

        $video_posts = get_posts( $args );

        // find the youtube thumb
        foreach($video_posts as $video_post) {
            $video_post = cutv_get_snaptube_post_data($video_post, $video_post->ID);
        }

        echo json_encode($video_posts);
    }
    die();
}
add_action('wp_ajax_cutv_get_source_video_posts', 'cutv_get_source_video_posts');

function cutv_get_source_videos($source_id, $meta = true) {

    global $wpdb;

    // find out how many videos are in that source?
    $source_videos = $wpdb->get_results("SELECT * FROM " . $wpdb->postmeta . " WHERE meta_key='wpvr_video_sourceId' AND meta_value=". $source_id );
    $response = null;
    $source_videos_extended = [];
    if (count($source_videos)) {

        foreach ($source_videos as $video) {
            $snaptube_video = get_post_meta( $video->post_id, '_cutv_snaptube_video', true );

            if (!$snaptube_video) {
                $response['unpublished_videos'][]= $video->post_id;
            } else {
                $response['published_videos'][] = $video->post_id;
                // if i want meta, i probably don't don't want the entire video info
                if (!$meta) {
                    echo '[cutv_get_source_videos] snaptube post id for ' . $video->post_id, ': ', $snaptube_video, "\n";
                    $response['videos'][] = cutv_get_snaptube_video( get_post_meta( $video->post_id, '_cutv_snaptube_video', true ));
                }
            }


        }
    }

    return $meta ? $response : count($source_videos);
}



function cutv_move_source_videos() {

    global $wpdb;

    if (isset($_REQUEST)) {

        $source_id = $_REQUEST['currentSrc'];
        $new_source = $_REQUEST['newSrc'];
        $source_videos = $wpdb->get_results("SELECT * FROM " . $wpdb->postmeta . " WHERE meta_key='wpvr_video_sourceId' AND meta_value=". $source_id );

        foreach ($source_videos as $video) {
            update_post_meta($video->post_id, 'wpvr_video_sourceId', $new_source);
            cutv_log(3, "video $video->post_id was moved to $new_source");
        }

        if ($_REQUEST['movePlaylists'] == true) {
            $new_playlists =  '';
            $currentSrc_YT_playlists = get_post_meta($source_id, 'wpvr_source_playlistIds_yt', true);
            $newSource_YT_playlists = get_post_meta($new_source, 'wpvr_source_playlistIds_yt', true);

            cutv_log(4, "current youtube playlists:  $currentSrc_YT_playlists");
            cutv_log(4, "new source youtube playlists:  $newSource_YT_playlists");


            $playlists = explode(',', $newSource_YT_playlists);
            foreach ($playlists as $playlist) {
                $playlists_exist = strrpos($currentSrc_YT_playlists, $newSource_YT_playlists);
                $new_playlists = ($playlists_exist === false) ? $currentSrc_YT_playlists.','.$newSource_YT_playlists : $currentSrc_YT_playlists;
            }

            update_post_meta($new_source, 'wpvr_source_playlistIds_yt', $new_playlists);

            cutv_log(3, "all source youtube playlists after update:  ". $new_playlists);

        }

        $wpdb->delete( $wpdb->postmeta, array( 'post_id' => $source_id ) );
        $wpdb->delete( $wpdb->posts, array( 'ID' => $source_id ) );

        // echo json_encode();

    }
    die();

}
add_action('wp_ajax_cutv_move_source_videos', 'cutv_move_source_videos');
