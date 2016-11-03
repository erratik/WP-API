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

        echo json_encode(end(cutv_get_channels()));

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
                echo '[wp_posts][wpvr video #'.$post_id.'] is now => '.$status, "\n";

                // find out if the video already exists as a snaptube video
                $wpvr_video = get_post($wpvr_video_id);


                $snaptube_post = $wpdb->get_row( "SELECT * FROM $wpdb->posts WHERE post_name = '".$wpvr_video->post_name."' AND post_type='videogallery'");

                $videos_to_convert = [];

                if ($snaptube_post != null) {
                    echo '[wp_posts] found wpvr video, #'.$snaptube_post->ID . ', '.$snaptube_post->post_title, "\n", "\$wpvr_video_id: $wpvr_video_id ", "\n";
//                    print_r(cutv_get_snaptube_video($snaptube_post->ID));
//                    echo  "\n" . PHP_EOL;
                    if ($status == 'publish') {
                        // UPDATE THE TAGS AND CATEGORIES
                        cutv_make_snaptube_cats($wpvr_video_id, cutv_get_snaptube_video($snaptube_post->ID));
                        cutv_make_snaptube_tags(get_the_tags($wpvr_video_id), $wpvr_video_id);
                        echo "cutv_get_snaptube_id(", $wpvr_video_id, "), get_the_tags(", $wpvr_video_id, ")", "\n";
                    } else {
                        $videos_to_convert[] = $post_id;
                    }

                } else {
                    echo '[wp_posts] no snaptube video corresponding to this wpvr video', "\n";
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

function cutv_convert_snaptube()
{

    // The $_REQUEST contains all the data sent via ajax
    if (isset($_REQUEST)) {
        global $wpdb;
        wp_suspend_cache_addition(true);

        echo count($_REQUEST['videos']), ' videos to edit!', "\n";

        foreach ($_REQUEST['videos'] as $video) {
            // GET THE WPVR VIDEO'S INFO
            $wpvr_id = $video['id'];

            // ATTEMPT TO PUBLISH WPVR VIDEO AS SNAPTUBE VIDEOS
            $wpvr_video = get_post($wpvr_id);

            // SET FILE (youtube link) FIELDS, USED TO ADD/REMOVE SNAPTUBE VIDEO
            $file = $video['file'];
            $snaptube_video = $wpdb->get_row( "SELECT vid FROM " . SNAPTUBE_VIDEOS ." WHERE file = '$file'");

            echo '('.$file.') should be added to snaptube', "\n";


            // FIELDS TO CREATE THE SNAPTUBE VIDEO FROM WPVR VIDEO
            $description = $wpvr_video->post_content;
            $post_date = $wpvr_video->post_date;
            $name = $wpvr_video->post_title;

            $vid = $wpdb->get_results("SELECT vid FROM " . SNAPTUBE_VIDEOS ." ORDER BY vid DESC LIMIT 1");
            $vid = $vid[0]->vid + 1;



            // CREATE POST DATA, USE THAT ID AS THE SLUG FOR THE VIDEO ROW
            $snaptube_video_post = array();
            $snaptube_video_post['post_title']    = $name;
            $snaptube_video_post['post_content']  = '[hdvideo id='.$vid.']';
            $snaptube_video_post['post_status']   = 'publish';
            $snaptube_video_post['post_author']   = $member_id;
            $snaptube_video_post['post_type']   = 'videogallery';
            $snaptube_video_post['post_type']   = 'videogallery';
            $snaptube_video_post['post_category'] = $categories;


            echo '(do: publish) add wpvr video => '. $vid;
            echo  "\n" . PHP_EOL;
            print_r($snaptube_video_post );
            echo  "\n" . PHP_EOL;


            // INSERT THE POST TO wp_posts AS A video_gallery, WITH UNIQUE VC POST META
            $post_id = wp_insert_post( $snaptube_video_post );
            if (!$post_id) {
                echo '***************************************************', "\n";
            }
            add_post_meta( $post_id, '_vc_post_settings', 'a:1:{s:10:"vc_grid_id";a:0:{}}', true );
            add_post_meta( $wpvr_id, '_cutv_snaptube_video', $post_id, true );
            add_post_meta( $wpvr_id, '_cutv_snaptube_referential', $vid, true );



            // FIELDS ADDED TO CREATE SNAPTUBE VIDEOS
            $slug = $wpvr_video->post_name;
            $member_id = $wpvr_video->post_author;
            $duration = $video['duration'];
            $image = $video['image'];
            $opimage = $video['opimage'];
            $link = $video['link'];

            // STANDARD SNAPTUBE VIDEO SHITg
            $featured       = 1;
            $download       = 0;
            $publish        = 1;
            $file_type      = 1;
            $islive         = 0;
            $amazon_buckets = 0;

            // GET ALL SNAPTUBE VIDEOS TO GET COUNT & ORDERING
            $videos = $wpdb->get_results("SELECT * FROM " . SNAPTUBE_VIDEOS);
            $ordering = count($videos) + 2;

            // INSERT INTO SNAPTUBE VIDEO TABLE (SNAPTUBE_VIDEOS)
            $query_vids = $wpdb->prepare("INSERT INTO " . SNAPTUBE_VIDEOS ." (vid, name, description, file, slug, file_type, duration, image, opimage, download, link, featured, post_date, publish, islive, member_id, ordering, amazon_buckets) VALUES ( %d, %s, %s, %s, %d, %d, %s, %s, %s, %d, %s, %d, %s, %d, %d, %d, %d, %d )",
                array($vid, sanitizeTitle($name), $description, $file, $post_id, $file_type, $duration, $image, $opimage, $download, $link, $featured, $post_date, $publish, $islive, $member_id, $ordering, $amazon_buckets)
            );

            // echo  "\n" . PHP_EOL;
            // print_r($query_vids);
            $wpdb->query($query_vids);



            // FIELDS TO CREATE THE SNAPTUBE VIDEO POST (THIS IS THE POST DISPLAYED ON THE SITE)
            $categories = cutv_make_snaptube_cats($wpvr_id, $snaptube_video);
            $tags = cutv_make_snaptube_tags($video['tags'], $wpvr_id);


            echo '[snaptube video converted] '. get_site_url() .'/wp-json/cutv/v2/videos/'. $wpvr_id, "\n";


        }

    }

    // Always die in functions echoing ajax content
    die();
}
add_action('wp_ajax_cutv_convert_snaptube', 'cutv_convert_snaptube');

function cutv_clear_snaptube_video()
{

    // The $_REQUEST contains all the data sent via ajax
    if (isset($_REQUEST)) {
        global $wpdb;

        print_r($_REQUEST['videos']);

        foreach ($_REQUEST['videos'] as $wpvr_post_id) {

            $snaptube_id = cutv_get_snaptube_post_id($wpvr_post_id);
            echo "-> cutv_clear_snaptube_video] snaptube id: $snaptube_id", "\n";

            $snaptube_video = cutv_get_snaptube_video($snaptube_id);

            if ($snaptube_video != null) {
                echo '[cutv_clear_snaptube_video] trying to clean out videogallery #', $snaptube_id, "\n";
                echo '[cutv_clear_snaptube_video] trying to clean out snaptube #', $snaptube_video->vid, "\n";

//            // CLEAN THE SNAPTUBE_VIDEOS
                $wpdb->delete( SNAPTUBE_VIDEOS, array( 'vid' => $snaptube_video->vid ) );
//
//            // CLEAN THE SNAPTUBE_PLAYLIST_RELATIONS (POSTS IN PLAYLISTS)
                $wpdb->delete( SNAPTUBE_PLAYLIST_RELATIONS, array( 'media_id' => $snaptube_video->vid ) );

//            // CLEAN THE SNAPTUBE_TAGS
                $wpdb->delete( SNAPTUBE_TAGS, array( 'media_id' => $snaptube_video->vid ) );

//            // CLEAN THE POSTS TABLE FROM THE SNAPTUBE VIDEOS
                $wpdb->delete( $wpdb->posts, array( 'ID' => $snaptube_id ) );
//
//            // CLEAN THE POST META
                $wpdb->delete( $wpdb->postmeta, array( 'post_id' => $wpvr_post_id ) );
            }
//            echo "-> cutv_clear_snaptube_video] snaptube video", "\n";
//            print_r($snaptube_video);
//            echo "\n";

            /*

                        $wpvr_post = $wpdb->get_row("SELECT * FROM $wpdb->posts WHERE ID = $wpvr_post_id");
                        $post_name = $wpvr_post->post_name;
                        $post_title = $wpvr_post->post_title;
            //            print_r($wpvr_post);
                        echo '[wp_posts] trying to clean out videogallery => '.$post_name, $wpvr_post_id, "\n";


                        // GET TEH SNAPTUBE VIDEO ID USING THE WPVR VIDEO POST SLUG (post_name)
                        $snaptube_post = $wpdb->get_row("SELECT * FROM $wpdb->posts WHERE post_name = '$post_name' AND post_type='videogallery'");
            //            print_r($snaptube_post);
                        echo "SELECT * FROM $wpdb->posts WHERE post_name = '$post_name' AND post_type='videogallery'", "\n";
                        $snaptube_id = $snaptube_post->ID;

                        // GET TEH SNAPTUBE VIDEO ID USING THE WPVR VIDEO POST SLUG (post_name)
                        $snaptube_video = $wpdb->get_row( "SELECT * FROM " . SNAPTUBE_VIDEOS ." WHERE slug = $snaptube_id");
            //            print_r($snaptube_video);

                        */
        }

        echo 'boom';

    }

    // Always die in functions echoing ajax content
    die();
}
add_action('wp_ajax_cutv_clear_snaptube_video', 'cutv_clear_snaptube_video');

function cutv_update_source_categories()
{

    // The $_REQUEST contains all the data sent via ajax
    if (isset($_REQUEST)) {
        global $wpdb;

        $channel_id = $_REQUEST['channel'];
        $current_source_count = $_REQUEST['source_count'];

        echo "-> cutv_update_source_categories :::: $current_source_count sources mapped to this category", "\n";

        $sources = json_decode($_REQUEST['sources']);

        foreach ($sources as $i => $source_id) {


            if ( ! add_term_meta( $channel_id, 'cutv_channel_enabled', $_REQUEST['enabled'], true)) {
                update_term_meta($channel_id, 'cutv_channel_enabled', $_REQUEST['enabled']);
            }

            // update the categories on each source
            $source_name = get_post_meta($source_id, 'wpvr_source_name', true);
            //get the sources
            $current_cats = get_numerics(get_post_meta($source_id, 'wpvr_source_postCats', true));

            $key = array_search($channel_id, $current_cats);
//            echo "key: ", $key, "\n";
            if ($key === FALSE) {
                echo "-> cutv_update_source_categories ~ need to add $source_name ($source_id) to channel ". $channel_id, "\n";

                if ( ! add_post_meta( $source_id, 'wpvr_source_postCats', '["'.$channel_id.'"]', true) ) {
                    update_post_meta($source_id, 'wpvr_source_postCats', '["'.$channel_id.'"]' );
                }

                if ( ! add_post_meta( $source_id, 'wpvr_source_postCats_', $channel_id, true)) {
                    update_post_meta($source_id, 'wpvr_source_postCats_', $channel_id);
                }

            } else {

                echo "-> cutv_update_source_categories :: $source_name ($source_id) is already mapped to channel ". $channel_id, "\n";
            }

            echo "SELECT post_id FROM " . $wpdb->postmeta ." WHERE meta_key='wpvr_video_sourceId' AND meta_value=$source_id", "\n";
            $source_videos = $wpdb->get_results("SELECT post_id FROM " . $wpdb->postmeta ." WHERE meta_key='wpvr_video_sourceId' AND meta_value=$source_id" );
            echo 'there are ' . count($source_videos) . " videos associated to $source_name ($source_id) ", "\n";


            foreach ($source_videos as $key => $video) {

                // find out if the video already exists as a snaptube video
                $wpvr_video_id = $video->post_id;

                $wpvr_video = get_post($wpvr_video_id);

                $snaptube_post = $wpdb->get_row( "SELECT * FROM $wpdb->posts WHERE post_name = '".$wpvr_video->post_name."' AND post_type='videogallery'");


                if ($snaptube_post != null) {
                    echo '[update_video_posts] found wpvr video, #'.$snaptube_post->ID . ', '.$snaptube_post->post_title, "\n", "\$wpvr_video_id: $wpvr_video_id ", "\n";
//
//                    // UPDATE THE TAGS AND CATEGORIES
                    cutv_make_snaptube_cats($wpvr_video_id, cutv_get_snaptube_video($snaptube_post->ID));


                } else {
                    echo "[update_video_posts] video not published yet, do nothing ( $wpvr_video_id )", "\n";

                }
            }


        }

        $removing_sources = json_decode($_REQUEST['removing_sources']);

        foreach ($removing_sources as $k => $source_id) {

            $test_cats = get_numerics(get_post_meta($source_id, 'wpvr_source_postCats', true));

            if(($key = array_search($channel_id, $test_cats)) !== FALSE) {
                //echo "[removing_sources] looking for channel id#$channel_id in source  $source_name ($source_id), found at position : ", array_search($channel_id, $test_cats), "\n";

                unset($test_cats[$key]);
            }

            $new_cat_str = "[\"";
            $new_cat_str .= implode("\",\"",$test_cats);
            $new_cat_str .= "\"]";


            if ( ! add_post_meta( $source_id, 'wpvr_source_postCats', $new_cat_str, true ) ) {
                update_post_meta($source_id, 'wpvr_source_postCats', $new_cat_str);
            }

        }


        print_r(cutv_get_sources_by_channel($channel_id));
        echo "data:".json_encode(cutv_get_sources_by_channel($channel_id));



    }

    // Always die in functions echoing ajax content
    die();
}

function cutv_make_snaptube_tags($tags, $wpvr_id) {
    global $wpdb;

    $vid = cutv_get_snaptube_vid($wpvr_id);

    // INSERT INTO SNAPTUBE_TAGS TABLE
    // @this is the category table sort of
    echo '[tags ('.count($tags).')] ', "\n";
    if ($tags != null) {

        $t = 0;
        $tag_str = '';
        $safe_concat_str = '';
        foreach ($tags as $tag_id) {
            // get the tag content
            $post_tags = get_term( $tag_id );
            if ($post_tags != null) {
                $tag_str .= $post_tags->name;
                $safe_concat_str .= hyphenize($post_tags->name);
                if (count($tags)-1 != $t) {
                    $tag_str .= ',';
                    $safe_concat_str .= '-';
                }
                $t++;
            }
        }

        // update the tags if the video already has tags (find media_id)
        $tags_exist = $wpdb->get_results('SELECT * FROM ' . SNAPTUBE_TAGS . " WHERE media_id=". $vid );

        if (count($tags_exist)) {
            $updated = $wpdb->update(
                SNAPTUBE_TAGS,
                array(
                    'seo_name' => strtolower($safe_concat_str),   // integer (number)
                    'tags_name' => $tag_str    // integer (number)
                ),
                array('media_id' => $vid),
                array(
                    '%s',
                    '%s'
                ),
                array('%d')
            );

        } else {


            // get the tag rows, getting the last vtag_id as new row key
            $snaptube_tags = $wpdb->get_results("SELECT * FROM " . SNAPTUBE_TAGS ." ORDER BY vtag_id DESC LIMIT 1");
            $new_tag_id = $snaptube_tags[0]->vtag_id + 1;
//            echo '[new tag id] ', $new_tag_id, "\n";

            $query_tags = $wpdb->prepare("INSERT INTO " . SNAPTUBE_TAGS . " (vtag_id, tags_name, seo_name, media_id) VALUES ( %d, %s, %s, %d) ",
                array($new_tag_id, $tag_str, strtolower($safe_concat_str), $vid)
            );
            echo $query_tags, "\n";
            $wpdb->query($query_tags);


        }

    }
}

function cutv_make_snaptube_cats($video_id, $snaptube_video) {

    global $wpdb;
    // INSERT INTO SNAPTUBE_PLAYLIST_RELATIONS TABLE
    // this is happening regardless of the post existing or not
    // this is happening regardless of the post existing or not
    // what is this video's source?
    $wpvr_video_source_id = get_post_meta($video_id, 'wpvr_video_sourceId', true);
    echo '[cutv_make_snaptube_cats::video source id]', $video_id, "=>", $wpvr_video_source_id, ': ', get_post_meta($video_id,  'wpvr_video_sourceName', true), "\n";

    //what are the source's categories
    $wpvr_video_source_cats = get_post_meta($wpvr_video_source_id, 'wpvr_source_postCats', true);

    // @this is the category table sort of
    $categories = get_numerics($wpvr_video_source_cats);
    print_r($categories);
    $snaptube_id = cutv_get_snaptube_vid($video_id);
    echo '[categories ('.count($categories).')] cutv_get_snaptube_id->'. $snaptube_id,  "\n";
//    print_r(cutv_get_snaptube_id($video_id));
//    echo cutv_get_snaptube_id($video_id);
    if (count($categories) == 0) {
        $categories = array(1);
    }


    foreach ($categories as $value) {

        $med2play = $wpdb->get_results("SELECT rel_id FROM " . SNAPTUBE_PLAYLIST_RELATIONS . " ORDER BY rel_id DESC LIMIT 1");


        $rel_id = $snaptube_id;
        $playlist_attr_exists = $wpdb->get_row("SELECT * FROM " . SNAPTUBE_PLAYLIST_RELATIONS . " WHERE media_id = " . $snaptube_id);

        if (null != $playlist_attr_exists) {

            echo "-> cutv_make_snaptube_cats] existing vid => " . $snaptube_id, ",  updating playlist value => " . $value;
            echo "\n" . PHP_EOL;

            $updated = $wpdb->update(
                SNAPTUBE_PLAYLIST_RELATIONS,
                array(
                    'playlist_id' => $value    // integer (number)
                ),
                array('media_id' => $snaptube_id ),
                array(
                    '%d'    // value2
                ),
                array('%d')
            );

            echo "\n" . PHP_EOL;
        }
        else {


            // INSERT INTO SNAPTUBE_PLAYLIST_RELATIONS TABLE
            echo "rel_id => " . $rel_id, ",  new playlist value => " . $value;
            echo "\n" . PHP_EOL;

            $vid = $wpdb->get_results("SELECT vid FROM " . SNAPTUBE_VIDEOS ." ORDER BY vid DESC LIMIT 1");
            $vid = $vid[0]->vid + 1;

            $query_med2play = $wpdb->prepare("INSERT INTO " . SNAPTUBE_PLAYLIST_RELATIONS . " (rel_id, media_id, playlist_id, porder, sorder) VALUES ( %d, %d, %d, %d, %d ) ",
                array($rel_id, $snaptube_id, $value, 0, 0)
            );

            $wpdb->query($query_med2play);

        }

    }


    return $categories;
}

function cutv_get_sources_info($abridged = true) {
    global $wpdb;

    $args = array(
        'post_type' => 'wpvr_source',
        'posts_per_page' => -1
    );


    // find out if the source is already in a channel(category)?


    // build source object
    $sourceObj = [];
    $sources = get_posts( $args );


    $sourceObj = [];

    foreach ($sources as $k => $source) {
//        echo  "[cutv_get_sources_info] ", get_post_meta($source->ID, 'wpvr_source_name', true), "\n";
//

//        $videoObject = cutv_get_source();
        $source_categories = get_numerics(get_post_meta($source->ID, 'wpvr_source_postCats', true));
//        $sources_categories = [];

        // only send unassigned sources (have no category)
        if (!count($source_categories)) {
            $unassignedSources[] = (object) [
                'name' => get_post_meta($source->ID, 'wpvr_source_name', true),
                'categories' => $source_categories,
                'ytPlaylist' => 'https://www.youtube.com/playlist?list=' . get_post_meta($source->ID, 'wpvr_source_playlistIds_yt', true),
                'ID' => $source->ID,
                'videos' => cutv_get_source($source->ID)
            ];
        } else {
            $assignedSources[] = (object) [
                'name' => get_post_meta($source->ID, 'wpvr_source_name', true),
                'categories' => $source_categories,
                'ytPlaylist' => 'https://www.youtube.com/playlist?list=' . get_post_meta($source->ID, 'wpvr_source_playlistIds_yt', true),
                'ID' => $source->ID,
                'videos' => cutv_get_source($source->ID)
            ];
        }

        if ($k == count($sources)-1) {
            $sourceObj['assigned'] = $assignedSources;
            $sourceObj['unassigned'] = $unassignedSources;
            $sourceObj['all'] = array_merge($assignedSources, $unassignedSources);

        }


    }
//    echo "*********** DASHBOARD ***********", "\n";
    return $sourceObj;
}
function get_meta_values( $key = '', $source_id) {

    global $wpdb;

    if( empty( $key ) )
        return;

    $r = $wpdb->get_results("SELECT post_id FROM wp_postmeta WHERE meta_key='wpvr_source_postCats' AND meta_value LIKE '%\"$source_id\"%'") ;
    foreach ($r as $meta_row) {
        $source_ids[] = $meta_row->post_id;
    }
    return $source_ids;
}
function cutv_get_sources_by_channel($channel_id, $abridged = true) {

    return get_meta_values( 'wpvr_source_postCats', $channel_id );

}

function cutv_get_source($source_id, $abridged = true) {

    $source_videos = cutv_get_source_videos($source_id);
    // print_r($source_videos);

    $videos = [];

    if ($abridged) {
        $videoObject = (object) [
            'unpublished_count' => count($source_videos['unpublished_videos']),
            'published_count' => count($source_videos['published_videos'])
        ];
    } else {
        if (count($source_videos['videos'])) {
            echo "we got published videos";
//        foreach ($source_videos->videos as $source_video) {
//            $videos = (object) [
//                'vid_referential' => $source_video->vid,
//                'name' => $source_video->name,
//                'description' => $source_video->description,
//                'wp_video_post' => $source_video->slug,
//                'featured' => $source_video->featured,
//                'video_thumbnail' => $source_video->image
//            ];
//        }
        }
        $videoObject = $source_videos;
    }

    return $videoObject;
}

function cutv_get_channels() {
    global $wpdb;
    $channels_rows = $wpdb->get_results("SELECT * FROM " . SNAPTUBE_PLAYLISTS ." WHERE pid > 1" );

    $channels = [];
    foreach ($channels_rows as $channel) {
        $channel->cutv_channel_img = get_term_meta( $channel->pid, 'cutv_channel_img', true );
        $channel->enabled = get_term_meta( $channel->pid, 'cutv_channel_enabled', true );
        $channels[] = $channel;
    };


    if (isset($_REQUEST) && $_REQUEST['json']) {
        header('Content-Type: application/json');
        echo json_encode($channels);
    } else {
        return $channels;
    }

    die();

}
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