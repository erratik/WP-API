<?php


function cutv_add_channel()
{

    // The $_REQUEST contains all the data sent via ajax
    if (isset($_REQUEST)) {
        global $wpdb;
        $channelName = $_REQUEST['channelName'];
        $description = $_REQUEST['description'];
        $slug = $_REQUEST['slug'];
        $cat_id = $_REQUEST['cat_id'];

        // Let's take the data that was sent and do something with it
//        if ( $channelName == 'Banana' ) {
//            $channelName = 'Apple';
//        }
//        $parent_term = term_exists( 'channel' ); // array is returned if taxonomy is given
//        $parent_term_id = $parent_term['term_id']; // get numeric term id

        $playlists = $wpdb->get_results( 'SELECT * FROM ' . SNAPTUBE_PLAYLISTS );

        $query = $wpdb->prepare("INSERT INTO " . SNAPTUBE_PLAYLISTS . " (pid, playlist_name, playlist_slugname, playlist_desc, is_publish, playlist_order) VALUES ( %d, %s, %s, %s, %d, %d )",
            array($cat_id, $channelName, $slug, $description, 1, count($playlists))
        );
        $wpdb->query($query);


        $playlistsUpdated = $wpdb->get_results('SELECT * FROM ' . SNAPTUBE_PLAYLISTS);

        // Now we'll return it to the javascript function
        // Anything outputted will be returned in the response
        print_r($playlistsUpdated);

        // If you're debugging, it might be useful to see what was sent in the $_REQUEST
        // print_r($_REQUEST);

    }

    // Always die in functions echoing ajax content
    die();
}
add_action('wp_ajax_cutv_add_channel', 'cutv_add_channel');

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
                        cutv_make_snaptube_tags(get_the_tags($wpvr_video_id), $snaptube_post->ID);
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


            // FIELDS TO CREATE THE SNAPTUBE VIDEO POST (THIS IS THE POST DISPLAYED ON THE SITE)
            $categories = cutv_make_snaptube_cats($wpvr_id, $snaptube_video);
            $tags = cutv_make_snaptube_tags($video['tags'], $snaptube_video->vid);

            // CREATE POST DATA, USE THAT ID AS THE SLUG FOR THE VIDEO ROW
            $snaptube_video_post = array();
            $snaptube_video_post['post_title']    = $name;
            $snaptube_video_post['post_content']  = '[hdvideo id='.$vid.']';
            $snaptube_video_post['post_status']   = 'publish';
            $snaptube_video_post['post_author']   = $member_id;
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
                array($vid, $name, $description, $file, $post_id, $file_type, $duration, $image, $opimage, $download, $link, $featured, $post_date, $publish, $islive, $member_id, $ordering, $amazon_buckets)
            );

            // echo  "\n" . PHP_EOL;
            // print_r($query_vids);
            $wpdb->query($query_vids);


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
            echo "[cutv_clear_snaptube_video] snaptube id: $snaptube_id", "\n";

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
//            echo "[cutv_clear_snaptube_video] snaptube video", "\n";
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
//        $current_sources = $_REQUEST['current_source_ids'];
        print_r($_REQUEST);
        echo "[cutv_update_source_categories] there are currently: ", count($current_source_count), " sources mapped to this category", "\n";
        $sources_mapped_to_channel = 0;


        foreach ($_REQUEST['sources'] as $i => $source_id) {


            // update the categories on each source
            $source_name = get_post_meta($source_id, 'wpvr_source_name', true);
            //get the sources
            $current_cats = get_numerics(get_post_meta($source_id, 'wpvr_source_postCats', true));

            $key = array_search($channel_id, $current_cats);
//            echo "key: ", $key, "\n";
            if ($key === FALSE) {
//                echo "need to add $source_id to channel ". $channel_id, "\n";
                add_post_meta( $source_id, 'wpvr_source_postCats', '["'.$channel_id.'"]', true );
                add_post_meta( $source_id, 'wpvr_source_postCats_', $channel_id, true );
            } else {

                echo "[cutv_update_source_categories]  $source_name ($source_id) is already mapped to channel ". $channel_id, "\n";
            }
            $sources_mapped_to_channel++;

            // find out how many sources have this source
//            echo "there are currently ", count($current_cats), " cats mapped to source ($source_id)", "\n";

        }
//        $test_cats = get_numerics(get_post_meta($source_id, 'wpvr_source_postCats', true));
//        print_r($test_cats);
        $new_cat_str = "\"";

        foreach ($_REQUEST['removing_sources'] as $k => $source_id) {


            $new_cat_str = "[\"";
            $test_cats = get_numerics(get_post_meta($source_id, 'wpvr_source_postCats', true));
//            unset($test_cats[$key]);
//            $key = array_/search($channel_id, $current_cats);
            echo "[removing_sources] looking for channel id#$channel_id in source  $source_name ($source_id), found at position : ", array_search($channel_id, $test_cats), "\n";

            print_r($test_cats);

            if(($key = array_search($channel_id, $test_cats)) !== FALSE) {
//                if ($k != count($_REQUEST['removing_sources'])) {
                    unset($test_cats[$key]);

            }
//            }

//            $new_cat_str = '"';
//            $new_cat_str += implode("\",\"",$test_cats);
//            $new_cat_str += '"';
//            echo $new_cat_str, "\n"   ;
            $new_cat_str .= implode("\",\"",$test_cats);

            $new_cat_str .= "\"]";
            echo $new_cat_str;

            if ( ! add_post_meta( $source_id, 'wpvr_source_postCats', $new_cat_str, true ) ) {
                update_post_meta($source_id, 'wpvr_source_postCats', $new_cat_str);
            }

        }




//        print_r($test_cats);


//        $new_cat_str += implode("\",\"",$current_cats);
//        $new_cat_str += '"';
//        echo $new_cat_str, "\n";
//
//        $source_rows = $wpdb->get_results("SELECT * FROM " . $wpdb->postmeta ." WHERE  meta_key='wpvr_source_postCats'");
//
//
//        $channel_source_count = 0;
//        foreach ($source_rows as $src) {
//
//            $src_cats = get_numerics($src->meta_value);
////            print_r($src_cats);
//
//            $key = array_search($channel_id, $src_cats);
////            echo "key: ", $key, "\n";
//            if ($key) {
//                $channel_source_count++;
//            }
//        }

//        echo $sources_mapped_to_channel, "\n";
//        echo $current_source_count, "\n";
//        if (count($_REQUEST['sources']) < $current_source_count) {
//            $new_cat_str = '"';
//            $new_cat_str += implode("\",\"",$_REQUEST['sources']);
//            $new_cat_str += '"';
//
//
//            echo $new_cat_str, "\n";
//
//        }



    }

    // Always die in functions echoing ajax content
    die();
}
add_action('wp_ajax_cutv_update_source_categories', 'cutv_update_source_categories');

function cutv_make_snaptube_tags($tags, $snaptube_id) {
    global $wpdb;

    $vid = cutv_get_snaptube_video($snaptube_id);
    $vid = $vid->vid;

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
            $wpdb->query($query_tags);


        }

    }
}

function cutv_make_snaptube_cats($video_id, $snaptube_video) {

    global $wpdb;
    // INSERT INTO SNAPTUBE_PLAYLIST_RELATIONS TABLE
    // this is happening regardless of the post existing or not
    // what is this video's source?
    $wpvr_video_source_id = get_post_meta($video_id, 'wpvr_video_sourceId', true);
    echo '[cutv_make_snaptube_cats::video source id]', $video_id, "=>", $wpvr_video_source_id, ': ', get_post_meta($video_id,  'wpvr_video_sourceName', true), "\n";

    //what are the source's categories
    $wpvr_video_source_cats = get_post_meta($wpvr_video_source_id, 'wpvr_source_postCats', true);

    // @this is the category table sort of
    $categories = get_numerics($wpvr_video_source_cats);
    echo '[categories ('.count($categories).')] ',  "\n";

    if (count($categories) == 0) {
        $categories = array(1);
    }


    foreach ($categories as $value) {

        $med2play = $wpdb->get_results("SELECT rel_id FROM " . SNAPTUBE_PLAYLIST_RELATIONS . " ORDER BY rel_id DESC LIMIT 1");

        $rel_id = $med2play[0]->rel_id + 1;

        $playlist_attr_exists = $wpdb->get_row("SELECT * FROM " . SNAPTUBE_PLAYLIST_RELATIONS . " WHERE media_id = " . $snaptube_video->vid);

        if (null != $playlist_attr_exists) {

            echo "[cutv_make_snaptube_cats] existing vid => " . $snaptube_video->vid , ",  updating playlist value => " . $value;
            echo "\n" . PHP_EOL;

            $updated = $wpdb->update(
                SNAPTUBE_PLAYLIST_RELATIONS,
                array(
                    'playlist_id' => $value    // integer (number)
                ),
                array('media_id' => $snaptube_video->vid ),
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
                array($rel_id, $snaptube_video->vid, $value, 0, 0)
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

        }


    }
//    echo "*********** DASHBOARD ***********", "\n";
    return $sourceObj;
}

function cutv_get_sources_by_channel($orphans = false, $abridged = true) {
    global $wpdb;

    $args = array(
        'post_type' => 'wpvr_source',
        'posts_per_page' => -1
    );


    // find out if the source is already in a channel(category)?


    // build source object
    $sourceObj = [];
    $sources = get_posts( $args );

    foreach ($sources as $source) {
//        echo  "[cutv_get_sources_info] ", get_post_meta($source->ID, 'wpvr_source_name', true), "\n";


//        $videoObject = cutv_get_source();
        $source_categories = get_numerics(get_post_meta($source->ID, 'wpvr_source_postCats', true));
//        $sources_categories = [];

        // only send unassigned sources (have no category)
        if (!count($source_categories)) {
            $sourceObj[] = (object) [
                'name' => get_post_meta($source->ID, 'wpvr_source_name', true),
                'categories' => $source_categories,
                'ytPlaylist' => 'https://www.youtube.com/playlist?list=' . get_post_meta($source->ID, 'wpvr_source_playlistIds_yt', true),
                'ID' => $source->ID,
                'videos' => cutv_get_source($source->ID)
            ];
        }
    }

//    echo "*********** DASHBOARD ***********", "\n";
    return $sourceObj;
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

    return $wpdb->get_results("SELECT * FROM " . SNAPTUBE_PLAYLISTS ." WHERE pid > 1" );

}