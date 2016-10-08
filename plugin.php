<?php
/**
 * Plugin Name: CUTV Admin API
 * Description: Forked version of WP REST API to manage WPVR in the themeforest Snaptube theme, using the Contus Video Gallery for Concordia University Television
 * Author: tayana jacques
 * Author URI: http://erratik.ca
 * Version: 0.0
 * Plugin URI: https://github.com/eratik/cutv-api
 */

/**
 * CUTV_REST_Controller class.
 */
if (!class_exists('CUTV_REST_Controller')) {
    require_once dirname(__FILE__) . '/lib/endpoints/class-cutv-rest-controller.php';
}

/**
 * CUTV_REST_Posts_Controller class.
 */
if (!class_exists('CUTV_REST_Posts_Controller')) {
    require_once dirname(__FILE__) . '/lib/endpoints/class-cutv-rest-posts-controller.php';
}

/**
 * CUTV_REST_Videos_Controller class.
 */
if (!class_exists('CUTV_REST_Videos_Controller')) {
    require_once dirname(__FILE__) . '/lib/endpoints/class-cutv-rest-videos-controller.php';
}

/**
 * CUTV_REST_Snaptubes_Controller class.
 */
if (!class_exists('CUTV_REST_Snaptubes_Controller')) {
    require_once dirname(__FILE__) . '/lib/endpoints/class-cutv-rest-snaptubes-controller.php';
}
/**
 * CUTV_REST_Attachments_Controller class.
 */
if (!class_exists('CUTV_REST_Attachments_Controller')) {
    require_once dirname(__FILE__) . '/lib/endpoints/class-cutv-rest-attachments-controller.php';
}

/**
 * CUTV_REST_Post_Types_Controller class.
 */
if (!class_exists('CUTV_REST_Post_Types_Controller')) {
    require_once dirname(__FILE__) . '/lib/endpoints/class-cutv-rest-post-types-controller.php';
}

/**
 * CUTV_REST_Post_Statuses_Controller class.
 */
if (!class_exists('CUTV_REST_Post_Statuses_Controller')) {
    require_once dirname(__FILE__) . '/lib/endpoints/class-cutv-rest-post-statuses-controller.php';
}

/**
 * CUTV_REST_Revisions_Controller class.
 */
if (!class_exists('CUTV_REST_Revisions_Controller')) {
    require_once dirname(__FILE__) . '/lib/endpoints/class-cutv-rest-revisions-controller.php';
}

/**
 * CUTV_REST_Taxonomies_Controller class.
 */
if (!class_exists('CUTV_REST_Taxonomies_Controller')) {
    require_once dirname(__FILE__) . '/lib/endpoints/class-cutv-rest-taxonomies-controller.php';
}

/**
 * CUTV_REST_Terms_Controller class.
 */
if (!class_exists('CUTV_REST_Terms_Controller')) {
    require_once dirname(__FILE__) . '/lib/endpoints/class-cutv-rest-terms-controller.php';
}

/**
 * CUTV_REST_Users_Controller class.
 */
if (!class_exists('CUTV_REST_Users_Controller')) {
    require_once dirname(__FILE__) . '/lib/endpoints/class-cutv-rest-users-controller.php';
}

/**
 * CUTV_REST_Comments_Controller class.
 */
if (!class_exists('CUTV_REST_Comments_Controller')) {
    require_once dirname(__FILE__) . '/lib/endpoints/class-cutv-rest-comments-controller.php';
}

/**
 * REST extras.
 */
include_once(dirname(__FILE__) . '/extras.php');
require_once(dirname(__FILE__) . '/core-integration.php');


add_filter('init', '_add_extra_cutv_api_post_type_arguments', 11);
add_action('init', '_add_extra_cutv_api_taxonomy_arguments', 11);
add_action('rest_api_init', 'create_initial_rest_routes', 0);

/**
 * Adds extra post type registration arguments.
 *
 * These attributes will eventually be committed to core.
 *
 * @since 4.4.0
 *
 * @global array $wp_post_types Registered post types.
 */
function _add_extra_cutv_api_post_type_arguments()
{
    global $wp_post_types;

    if (isset($wp_post_types['post'])) {
        $wp_post_types['post']->show_in_rest = true;
        $wp_post_types['post']->rest_base = 'posts';
        $wp_post_types['post']->rest_controller_class = 'CUTV_REST_Posts_Controller';
    }


    if (isset($wp_post_types['videogallery'])) {
        $wp_post_types['videogallery']->show_in_rest = true;
        $wp_post_types['videogallery']->rest_base = 'snaptubes';
        $wp_post_types['videogallery']->rest_controller_class = 'CUTV_REST_Snaptubes_Controller';
    }

    if (isset($wp_post_types['wpvr_video'])) {
        $wp_post_types['wpvr_video']->show_in_rest = true;
        $wp_post_types['wpvr_video']->rest_base = 'videos';
        $wp_post_types['wpvr_video']->rest_controller_class = 'CUTV_REST_Videos_Controller';
    }
    if (isset($wp_post_types['page'])) {
        $wp_post_types['page']->show_in_rest = true;
        $wp_post_types['page']->rest_base = 'pages';
        $wp_post_types['page']->rest_controller_class = 'CUTV_REST_Posts_Controller';
    }

    if (isset($wp_post_types['attachment'])) {
        $wp_post_types['attachment']->show_in_rest = true;
        $wp_post_types['attachment']->rest_base = 'media';
        $wp_post_types['attachment']->rest_controller_class = 'CUTV_REST_Attachments_Controller';
    }
}

/**
 * Adds extra taxonomy registration arguments.
 *
 * These attributes will eventually be committed to core.
 *
 * @since 4.4.0
 *
 * @global array $wp_taxonomies Registered taxonomies.
 */
function _add_extra_cutv_api_taxonomy_arguments()
{
    global $wp_taxonomies;

    if (isset($wp_taxonomies['category'])) {
        $wp_taxonomies['category']->show_in_rest = true;
        $wp_taxonomies['category']->rest_base = 'categories';
        $wp_taxonomies['category']->rest_controller_class = 'CUTV_REST_Terms_Controller';
    }

    if (isset($wp_taxonomies['post_tag'])) {
        $wp_taxonomies['post_tag']->show_in_rest = true;
        $wp_taxonomies['post_tag']->rest_base = 'tags';
        $wp_taxonomies['post_tag']->rest_controller_class = 'CUTV_REST_Terms_Controller';
    }
}

if (!function_exists('create_initial_rest_routes')) {
    /**
     * Registers default REST API routes.
     *
     * @since 4.4.0
     */
    function create_initial_rest_routes()
    {

        foreach (get_post_types(array('show_in_rest' => true), 'objects') as $post_type) {
            $class = !empty($post_type->rest_controller_class) ? $post_type->rest_controller_class : 'CUTV_REST_Posts_Controller';

            if (!class_exists($class)) {
                continue;
            }
            $controller = new $class($post_type->name);
            if (!is_subclass_of($controller, 'CUTV_REST_Controller')) {
                continue;
            }

            $controller->register_routes();

            if (post_type_supports($post_type->name, 'revisions')) {
                $revisions_controller = new CUTV_REST_Revisions_Controller($post_type->name);
                $revisions_controller->register_routes();
            }
        }

        // Post types.
        $controller = new CUTV_REST_Post_Types_Controller;
        $controller->register_routes();

        // Post statuses.
        $controller = new CUTV_REST_Post_Statuses_Controller;
        $controller->register_routes();

        // Taxonomies.
        $controller = new CUTV_REST_Taxonomies_Controller;
        $controller->register_routes();

        // Terms.
        foreach (get_taxonomies(array('show_in_rest' => true), 'object') as $taxonomy) {
            $class = !empty($taxonomy->rest_controller_class) ? $taxonomy->rest_controller_class : 'CUTV_REST_Terms_Controller';

            if (!class_exists($class)) {
                continue;
            }
            $controller = new $class($taxonomy->name);
            if (!is_subclass_of($controller, 'CUTV_REST_Controller')) {
                continue;
            }

            $controller->register_routes();
        }

        // Users.
        $controller = new CUTV_REST_Users_Controller;
        $controller->register_routes();

        // Comments.
        $controller = new CUTV_REST_Comments_Controller;
        $controller->register_routes();
    }
}

if (!function_exists('rest_authorization_required_code')) {
    /**
     * Returns a contextual HTTP error code for authorization failure.
     *
     * @return integer
     */
    function rest_authorization_required_code()
    {
        return is_user_logged_in() ? 403 : 401;
    }
}

if (!function_exists('register_rest_field')) {
    /**
     * Registers a new field on an existing WordPress object type.
     *
     * @global array $wp_rest_additional_fields Holds registered fields, organized
     *                                          by object type.
     *
     * @param string|array $object_type Object(s) the field is being registered
     *                                  to, "post"|"term"|"comment" etc.
     * @param string $attribute The attribute name.
     * @param array $args {
     *     Optional. An array of arguments used to handle the registered field.
     *
     * @type string|array|null $get_callback Optional. The callback function used to retrieve the field
     *                                              value. Default is 'null', the field will not be returned in
     *                                              the response.
     * @type string|array|null $update_callback Optional. The callback function used to set and update the
     *                                              field value. Default is 'null', the value cannot be set or
     *                                              updated.
     * @type string|array|null $schema Optional. The callback function used to create the schema for
     *                                              this field. Default is 'null', no schema entry will be returned.
     * }
     */
    function register_rest_field($object_type, $attribute, $args = array())
    {
        $defaults = array(
            'get_callback' => null,
            'update_callback' => null,
            'schema' => null,
        );

        $args = wp_parse_args($args, $defaults);

        global $wp_rest_additional_fields;

        $object_types = (array)$object_type;

        foreach ($object_types as $object_type) {
            $wp_rest_additional_fields[$object_type][$attribute] = $args;
        }
    }
}

if (!function_exists('register_api_field')) {
    /**
     * Backwards compat shim
     */
    function register_api_field($object_type, $attributes, $args = array())
    {
        _deprecated_function('register_api_field', 'WPAPI-2.0', 'register_rest_field');
        register_rest_field($object_type, $attributes, $args);
    }
}

if (!function_exists('rest_validate_request_arg')) {
    /**
     * Validate a request argument based on details registered to the route.
     *
     * @param  mixed $value
     * @param  CUTV_REST_Request $request
     * @param  string $param
     * @return WP_Error|boolean
     */
    function rest_validate_request_arg($value, $request, $param)
    {

        $attributes = $request->get_attributes();
        if (!isset($attributes['args'][$param]) || !is_array($attributes['args'][$param])) {
            return true;
        }
        $args = $attributes['args'][$param];

        if (!empty($args['enum'])) {
            if (!in_array($value, $args['enum'])) {
                return new WP_Error('rest_invalid_param', sprintf(__('%s is not one of %s'), $param, implode(', ', $args['enum'])));
            }
        }

        if ('integer' === $args['type'] && !is_numeric($value)) {
            return new WP_Error('rest_invalid_param', sprintf(__('%s is not of type %s'), $param, 'integer'));
        }

        if ('string' === $args['type'] && !is_string($value)) {
            return new WP_Error('rest_invalid_param', sprintf(__('%s is not of type %s'), $param, 'string'));
        }

        if (isset($args['format'])) {
            switch ($args['format']) {
                case 'date-time' :
                    if (!rest_parse_date($value)) {
                        return new WP_Error('rest_invalid_date', __('The date you provided is invalid.'));
                    }
                    break;

                case 'email' :
                    if (!is_email($value)) {
                        return new WP_Error('rest_invalid_email', __('The email address you provided is invalid.'));
                    }
                    break;
            }
        }

        if (in_array($args['type'], array('numeric', 'integer')) && (isset($args['minimum']) || isset($args['maximum']))) {
            if (isset($args['minimum']) && !isset($args['maximum'])) {
                if (!empty($args['exclusiveMinimum']) && $value <= $args['minimum']) {
                    return new WP_Error('rest_invalid_param', sprintf(__('%s must be greater than %d (exclusive)'), $param, $args['minimum']));
                } else if (empty($args['exclusiveMinimum']) && $value < $args['minimum']) {
                    return new WP_Error('rest_invalid_param', sprintf(__('%s must be greater than %d (inclusive)'), $param, $args['minimum']));
                }
            } else if (isset($args['maximum']) && !isset($args['minimum'])) {
                if (!empty($args['exclusiveMaximum']) && $value >= $args['maximum']) {
                    return new WP_Error('rest_invalid_param', sprintf(__('%s must be less than %d (exclusive)'), $param, $args['maximum']));
                } else if (empty($args['exclusiveMaximum']) && $value > $args['maximum']) {
                    return new WP_Error('rest_invalid_param', sprintf(__('%s must be less than %d (inclusive)'), $param, $args['maximum']));
                }
            } else if (isset($args['maximum']) && isset($args['minimum'])) {
                if (!empty($args['exclusiveMinimum']) && !empty($args['exclusiveMaximum'])) {
                    if ($value >= $args['maximum'] || $value <= $args['minimum']) {
                        return new WP_Error('rest_invalid_param', sprintf(__('%s must be between %d (exclusive) and %d (exclusive)'), $param, $args['minimum'], $args['maximum']));
                    }
                } else if (empty($args['exclusiveMinimum']) && !empty($args['exclusiveMaximum'])) {
                    if ($value >= $args['maximum'] || $value < $args['minimum']) {
                        return new WP_Error('rest_invalid_param', sprintf(__('%s must be between %d (inclusive) and %d (exclusive)'), $param, $args['minimum'], $args['maximum']));
                    }
                } else if (!empty($args['exclusiveMinimum']) && empty($args['exclusiveMaximum'])) {
                    if ($value > $args['maximum'] || $value <= $args['minimum']) {
                        return new WP_Error('rest_invalid_param', sprintf(__('%s must be between %d (exclusive) and %d (inclusive)'), $param, $args['minimum'], $args['maximum']));
                    }
                } else if (empty($args['exclusiveMinimum']) && empty($args['exclusiveMaximum'])) {
                    if ($value > $args['maximum'] || $value < $args['minimum']) {
                        return new WP_Error('rest_invalid_param', sprintf(__('%s must be between %d (inclusive) and %d (inclusive)'), $param, $args['minimum'], $args['maximum']));
                    }
                }
            }
        }

        return true;
    }
}

if (!function_exists('rest_sanitize_request_arg')) {
    /**
     * Sanitize a request argument based on details registered to the route.
     *
     * @param  mixed $value
     * @param  CUTV_REST_Request $request
     * @param  string $param
     * @return mixed
     */
    function rest_sanitize_request_arg($value, $request, $param)
    {

        $attributes = $request->get_attributes();
        if (!isset($attributes['args'][$param]) || !is_array($attributes['args'][$param])) {
            return $value;
        }
        $args = $attributes['args'][$param];

        if ('integer' === $args['type']) {
            return (int)$value;
        }

        if (isset($args['format'])) {
            switch ($args['format']) {
                case 'date-time' :
                    return sanitize_text_field($value);

                case 'email' :
                    /*
                     * sanitize_email() validates, which would be unexpected
                     */
                    return sanitize_text_field($value);

                case 'uri' :
                    return esc_url_raw($value);
            }
        }

        return $value;
    }

}

global $wpdb;
define('CUTV_MAIN_FILE', __FILE__);
define('SNAPTUBE_VIDEOS', $wpdb->prefix . 'hdflvvideoshare');
define('SNAPTUBE_PLAYLISTS', $wpdb->prefix . 'hdflvvideoshare_playlist');
define('SNAPTUBE_PLAYLIST_RELATIONS', $wpdb->prefix . 'hdflvvideoshare_med2play');
define('SNAPTUBE_TAGS', $wpdb->prefix . 'hdflvvideoshare_tags');

/* Including functions definitions */
require_once('cutv.definitions.php');
require_once('cutv.hooks.php');
require_once('cutv.functions.php');
/* Including Sources CPT definitions */
//require_once( 'includes/cutv.sources.php' );


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

function cutv_publish_wpvr_videos()
{

    // The $_REQUEST contains all the data sent via ajax
    if (isset($_REQUEST)) {
        global $wpdb;
        foreach ($_REQUEST['ids'] as $wpvr_video_id) {
            wp_update_post(array(
                'ID' => $wpvr_video_id,
                'post_status' => 'publish'
            ));
        }

    }

    // Always die in functions echoing ajax content
    die();
}
add_action('wp_ajax_cutv_publish_wpvr_videos', 'cutv_publish_wpvr_videos');

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


/**
 *
 */
function cutv_get_snaptube_post_id($id) {
    global $wpdb;

    echo "[cutv_get_snaptube_video_id] >>> getting snaptube video for wpvr video: $id", "\n";
    $wpvr_post = get_post($id);
    $post_name = $wpvr_post->post_name;

    // GET TEH SNAPTUBE VIDEO ID USING THE WPVR VIDEO POST SLUG (post_name)
    $snaptube_post = $wpdb->get_row("SELECT * FROM $wpdb->posts WHERE post_name = '$post_name' AND post_type='videogallery'");
//    echo "SELECT * FROM $wpdb->posts WHERE post_name = '$post_name' AND post_type='videogallery'", "\n";
//    print_r($snaptube_post);
    $snaptube_id = $snaptube_post->ID;
    echo "[cutv_get_snaptube_video_id] snaptube id: $snaptube_id", "\n";

    return $snaptube_id;


}

function cutv_get_snaptube_video($id) {
    global $wpdb;
    echo "[cutv_get_snaptube_video] -------- getting snaptube video for wpvr video post: $id", "\n";
    echo "SELECT * FROM " . SNAPTUBE_VIDEOS ." WHERE slug = $id", "\n";
    // GET TEH SNAPTUBE VIDEO USING THE WPVR VIDEO ID
    return $wpdb->get_row( "SELECT * FROM " . SNAPTUBE_VIDEOS ." WHERE slug = $id");
}

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


            // INSERT THE POST TO wp_posts AS A video_gallery, WITH UNIQUE VC POST META
            $post_id = wp_insert_post( $snaptube_video_post );
            add_post_meta( $post_id, '_vc_post_settings', 'a:1:{s:10:"vc_grid_id";a:0:{}}', true );



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

function hyphenize($string) {
    return

        strtolower(
            preg_replace(
                array('#[\\s-]+#', '#[^A-Za-z0-9\-]+#'),
                array('-', ''),
                ##     cleanString(
                urldecode($string)
            ##     )
            )
        )
        ;
}

function get_numerics ($str) {
    preg_match_all('/\d+/', $str, $matches);
    return $matches[0];
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

function cutv_api_init()
{

	wp_localize_script( 'cutv-api', 'wpApiSettings', array( 'root' => esc_url_raw( rest_url() ), 'nonce' => wp_create_nonce( 'wp_rest' ) ) );
	wp_enqueue_script( 'cutv-api' );

}
add_action('init', 'cutv_api_init');

//create a function that will attach our new 'channel' taxonomy to the 'post' post type
//function add_channel_taxonomy_to_post()
//{
//
//    //set the name of the taxonomy
//    $taxonomy = 'channel';
//    //set the post types for the taxonomy
//    $object_type = 'page';
//
//    //populate our array of names for our taxonomy
//    $labels = array(
//        'name' => 'Channels',
//        'singular_name' => 'Channel',
//        'search_items' => 'Search Channels',
//        'all_items' => 'All Channels',
//        'parent_item' => 'Parent Channel',
//        'parent_item_colon' => 'Parent Channel:',
//        'update_item' => 'Update Channel',
//        'edit_item' => 'Edit Channel',
//        'add_new_item' => 'Add New Channel',
//        'new_item_name' => 'New Channel Name',
//        'menu_name' => 'Channel'
//    );
//
//    //define arguments to be used
//    $args = array(
//        'labels' => $labels,
//        'hierarchical' => true,
//        'show_ui' => true,
//        'how_in_nav_menus' => true,
//        'public' => true,
//        'show_admin_column' => true,
//        'query_var' => true,
//        'rewrite' => array('slug' => 'channel')
//    );
//
//    //call the register_taxonomy function
//    register_taxonomy($taxonomy, $object_type, $args);
//}
//add_action('init', 'add_channel_taxonomy_to_post');
// If you wanted to also use the function for non-logged in users (in a theme for example)
// add_action( 'wp_ajax_nopriv_cutv_add_channel', 'cutv_add_channel' );

// Load plugin class files
require_once('includes/class-wordpress-plugin-template.php');
require_once('includes/class-wordpress-plugin-template-settings.php');

// Load plugin libraries
require_once('includes/lib/class-wordpress-plugin-template-admin-api.php');
require_once('includes/lib/class-wordpress-plugin-template-post-type.php');
require_once('includes/lib/class-wordpress-plugin-template-taxonomy.php');


/**
 * Returns the main instance of CUTV_Channel to prevent the need to use globals.
 *
 * @since  0.0.1
 * @return object CUTV_Channel
 */
function CUTV_Channel()
{
    $instance = CUTV_Channel::instance(__FILE__, '1.0.0');

    if (is_null($instance->settings)) {
        $instance->settings = CUTV_Channel_Settings::instance($instance);
    }

    return $instance;
}

CUTV_Channel();

