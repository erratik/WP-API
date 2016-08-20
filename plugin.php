<?php
/**
 * Plugin Name: CUTV Admin API
 * Description: Forked version of WP REST API to manage WPVR in the Snaptube theme, using the Contus Video Gallery for Concordia University Television
 * Author: tayana jacques
 * Author URI: http://erratik.ca
 * Version: 0.0
 * Plugin URI: https://github.com/eratik/cutv-api
 */

/**
 * CUTV_REST_Controller class.
 */
if ( ! class_exists( 'CUTV_REST_Controller' ) ) {
    require_once dirname(__FILE__) . '/lib/endpoints/class-cutv-rest-controller.php';
}

/**
 * CUTV_REST_Posts_Controller class.
 */
if ( ! class_exists( 'CUTV_REST_Posts_Controller' ) ) {
    require_once dirname(__FILE__) . '/lib/endpoints/class-cutv-rest-posts-controller.php';
}

/**
 * CUTV_REST_Videos_Controller class.
 */
if ( ! class_exists( 'CUTV_REST_Videos_Controller' ) ) {
    require_once dirname(__FILE__) . '/lib/endpoints/class-cutv-rest-videos-controller.php';
}

/**
 * CUTV_REST_Snaptubes_Controller class.
 */
if ( ! class_exists( 'CUTV_REST_Snaptubes_Controller' ) ) {
    require_once dirname(__FILE__) . '/lib/endpoints/class-cutv-rest-snaptubes-controller.php';
}
/**
 * CUTV_REST_Attachments_Controller class.
 */
if ( ! class_exists( 'CUTV_REST_Attachments_Controller' ) ) {
    require_once dirname(__FILE__) . '/lib/endpoints/class-cutv-rest-attachments-controller.php';
}

/**
 * CUTV_REST_Post_Types_Controller class.
 */
if ( ! class_exists( 'CUTV_REST_Post_Types_Controller' ) ) {
    require_once dirname(__FILE__) . '/lib/endpoints/class-cutv-rest-post-types-controller.php';
}

/**
 * CUTV_REST_Post_Statuses_Controller class.
 */
if ( ! class_exists( 'CUTV_REST_Post_Statuses_Controller' ) ) {
    require_once dirname(__FILE__) . '/lib/endpoints/class-cutv-rest-post-statuses-controller.php';
}

/**
 * CUTV_REST_Revisions_Controller class.
 */
if ( ! class_exists( 'CUTV_REST_Revisions_Controller' ) ) {
    require_once dirname(__FILE__) . '/lib/endpoints/class-cutv-rest-revisions-controller.php';
}

/**
 * CUTV_REST_Taxonomies_Controller class.
 */
if ( ! class_exists( 'CUTV_REST_Taxonomies_Controller' ) ) {
    require_once dirname(__FILE__) . '/lib/endpoints/class-cutv-rest-taxonomies-controller.php';
}

/**
 * CUTV_REST_Terms_Controller class.
 */
if ( ! class_exists( 'CUTV_REST_Terms_Controller' ) ) {
    require_once dirname(__FILE__) . '/lib/endpoints/class-cutv-rest-terms-controller.php';
}

/**
 * CUTV_REST_Users_Controller class.
 */
if ( ! class_exists( 'CUTV_REST_Users_Controller' ) ) {
    require_once dirname(__FILE__) . '/lib/endpoints/class-cutv-rest-users-controller.php';
}

/**
 * CUTV_REST_Comments_Controller class.
 */
if ( ! class_exists( 'CUTV_REST_Comments_Controller' ) ) {
    require_once dirname(__FILE__) . '/lib/endpoints/class-cutv-rest-comments-controller.php';
}

/**
 * REST extras.
 */
include_once( dirname( __FILE__ ) . '/extras.php' );
require_once( dirname( __FILE__ ) . '/core-integration.php' );

add_filter( 'init', '_add_extra_cutv_api_post_type_arguments', 11 );
add_action( 'init', '_add_extra_cutv_api_taxonomy_arguments', 11 );
add_action( 'rest_api_init', 'create_initial_rest_routes', 0 );

/**
 * Adds extra post type registration arguments.
 *
 * These attributes will eventually be committed to core.
 *
 * @since 4.4.0
 *
 * @global array $wp_post_types Registered post types.
 */
function _add_extra_cutv_api_post_type_arguments() {
    global $wp_post_types;

    if ( isset( $wp_post_types['post'] ) ) {
        $wp_post_types['post']->show_in_rest = true;
        $wp_post_types['post']->rest_base = 'posts';
        $wp_post_types['post']->rest_controller_class = 'CUTV_REST_Posts_Controller';
    }

    if ( isset( $wp_post_types['wpvr_video'] ) ) {
        $wp_post_types['wpvr_video']->show_in_rest = true;
        $wp_post_types['wpvr_video']->rest_base = 'videos';
        $wp_post_types['wpvr_video']->rest_controller_class = 'CUTV_REST_Videos_Controller';
    }

    if ( isset( $wp_post_types['videogallery'] ) ) {
        $wp_post_types['videogallery']->show_in_rest = true;
        $wp_post_types['videogallery']->rest_base = 'snaptubes';
        $wp_post_types['videogallery']->rest_controller_class = 'CUTV_REST_Snaptubes_Controller';
    }

    if ( isset( $wp_post_types['page'] ) ) {
        $wp_post_types['page']->show_in_rest = true;
        $wp_post_types['page']->rest_base = 'pages';
        $wp_post_types['page']->rest_controller_class = 'CUTV_REST_Posts_Controller';
    }

    if ( isset( $wp_post_types['attachment'] ) ) {
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
function _add_extra_cutv_api_taxonomy_arguments() {
    global $wp_taxonomies;

    if ( isset( $wp_taxonomies['category'] ) ) {
        $wp_taxonomies['category']->show_in_rest = true;
        $wp_taxonomies['category']->rest_base = 'categories';
        $wp_taxonomies['category']->rest_controller_class = 'CUTV_REST_Terms_Controller';
    }

    if ( isset( $wp_taxonomies['post_tag'] ) ) {
        $wp_taxonomies['post_tag']->show_in_rest = true;
        $wp_taxonomies['post_tag']->rest_base = 'tags';
        $wp_taxonomies['post_tag']->rest_controller_class = 'CUTV_REST_Terms_Controller';
    }
}

if ( ! function_exists( 'create_initial_rest_routes' ) ) {
    /**
     * Registers default REST API routes.
     *
     * @since 4.4.0
     */
    function create_initial_rest_routes() {

        foreach ( get_post_types( array( 'show_in_rest' => true ), 'objects' ) as $post_type ) {
            $class = ! empty( $post_type->rest_controller_class ) ? $post_type->rest_controller_class : 'CUTV_REST_Posts_Controller';

            if ( ! class_exists( $class ) ) {
                continue;
            }
            $controller = new $class( $post_type->name );
            if ( ! is_subclass_of( $controller, 'CUTV_REST_Controller' ) ) {
                continue;
            }

            $controller->register_routes();

            if ( post_type_supports( $post_type->name, 'revisions' ) ) {
                $revisions_controller = new CUTV_REST_Revisions_Controller( $post_type->name );
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
        foreach ( get_taxonomies( array( 'show_in_rest' => true ), 'object' ) as $taxonomy ) {
            $class = ! empty( $taxonomy->rest_controller_class ) ? $taxonomy->rest_controller_class : 'CUTV_REST_Terms_Controller';

            if ( ! class_exists( $class ) ) {
                continue;
            }
            $controller = new $class( $taxonomy->name );
            if ( ! is_subclass_of( $controller, 'CUTV_REST_Controller' ) ) {
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

if ( ! function_exists( 'rest_authorization_required_code' ) ) {
    /**
     * Returns a contextual HTTP error code for authorization failure.
     *
     * @return integer
     */
    function rest_authorization_required_code() {
        return is_user_logged_in() ? 403 : 401;
    }
}

if ( ! function_exists( 'register_rest_field' ) ) {
    /**
     * Registers a new field on an existing WordPress object type.
     *
     * @global array $wp_rest_additional_fields Holds registered fields, organized
     *                                          by object type.
     *
     * @param string|array $object_type Object(s) the field is being registered
     *                                  to, "post"|"term"|"comment" etc.
     * @param string $attribute         The attribute name.
     * @param array  $args {
     *     Optional. An array of arguments used to handle the registered field.
     *
     *     @type string|array|null $get_callback    Optional. The callback function used to retrieve the field
     *                                              value. Default is 'null', the field will not be returned in
     *                                              the response.
     *     @type string|array|null $update_callback Optional. The callback function used to set and update the
     *                                              field value. Default is 'null', the value cannot be set or
     *                                              updated.
     *     @type string|array|null $schema          Optional. The callback function used to create the schema for
     *                                              this field. Default is 'null', no schema entry will be returned.
     * }
     */
    function register_rest_field( $object_type, $attribute, $args = array() ) {
        $defaults = array(
            'get_callback'    => null,
            'update_callback' => null,
            'schema'          => null,
        );

        $args = wp_parse_args( $args, $defaults );

        global $wp_rest_additional_fields;

        $object_types = (array) $object_type;

        foreach ( $object_types as $object_type ) {
            $wp_rest_additional_fields[ $object_type ][ $attribute ] = $args;
        }
    }
}

if ( ! function_exists( 'register_api_field' ) ) {
    /**
     * Backwards compat shim
     */
    function register_api_field( $object_type, $attributes, $args = array() ) {
        _deprecated_function( 'register_api_field', 'WPAPI-2.0', 'register_rest_field' );
        register_rest_field( $object_type, $attributes, $args );
    }
}

if ( ! function_exists( 'rest_validate_request_arg' ) ) {
    /**
     * Validate a request argument based on details registered to the route.
     *
     * @param  mixed            $value
     * @param  CUTV_REST_Request  $request
     * @param  string           $param
     * @return WP_Error|boolean
     */
    function rest_validate_request_arg( $value, $request, $param ) {

        $attributes = $request->get_attributes();
        if ( ! isset( $attributes['args'][ $param ] ) || ! is_array( $attributes['args'][ $param ] ) ) {
            return true;
        }
        $args = $attributes['args'][ $param ];

        if ( ! empty( $args['enum'] ) ) {
            if ( ! in_array( $value, $args['enum'] ) ) {
                return new WP_Error( 'rest_invalid_param', sprintf( __( '%s is not one of %s' ), $param, implode( ', ', $args['enum'] ) ) );
            }
        }

        if ( 'integer' === $args['type'] && ! is_numeric( $value ) ) {
            return new WP_Error( 'rest_invalid_param', sprintf( __( '%s is not of type %s' ), $param, 'integer' ) );
        }

        if ( 'string' === $args['type'] && ! is_string( $value ) ) {
            return new WP_Error( 'rest_invalid_param', sprintf( __( '%s is not of type %s' ), $param, 'string' ) );
        }

        if ( isset( $args['format'] ) ) {
            switch ( $args['format'] ) {
                case 'date-time' :
                    if ( ! rest_parse_date( $value ) ) {
                        return new WP_Error( 'rest_invalid_date', __( 'The date you provided is invalid.' ) );
                    }
                    break;

                case 'email' :
                    if ( ! is_email( $value ) ) {
                        return new WP_Error( 'rest_invalid_email', __( 'The email address you provided is invalid.' ) );
                    }
                    break;
            }
        }

        if ( in_array( $args['type'], array( 'numeric', 'integer' ) ) && ( isset( $args['minimum'] ) || isset( $args['maximum'] ) ) ) {
            if ( isset( $args['minimum'] ) && ! isset( $args['maximum'] ) ) {
                if ( ! empty( $args['exclusiveMinimum'] ) && $value <= $args['minimum'] ) {
                    return new WP_Error( 'rest_invalid_param', sprintf( __( '%s must be greater than %d (exclusive)' ), $param, $args['minimum'] ) );
                } else if ( empty( $args['exclusiveMinimum'] ) && $value < $args['minimum'] ) {
                    return new WP_Error( 'rest_invalid_param', sprintf( __( '%s must be greater than %d (inclusive)' ), $param, $args['minimum'] ) );
                }
            } else if ( isset( $args['maximum'] ) && ! isset( $args['minimum'] ) ) {
                if ( ! empty( $args['exclusiveMaximum'] ) && $value >= $args['maximum'] ) {
                    return new WP_Error( 'rest_invalid_param', sprintf( __( '%s must be less than %d (exclusive)' ), $param, $args['maximum'] ) );
                } else if ( empty( $args['exclusiveMaximum'] ) && $value > $args['maximum'] ) {
                    return new WP_Error( 'rest_invalid_param', sprintf( __( '%s must be less than %d (inclusive)' ), $param, $args['maximum'] ) );
                }
            } else if ( isset( $args['maximum'] ) && isset( $args['minimum'] ) ) {
                if ( ! empty( $args['exclusiveMinimum'] ) && ! empty( $args['exclusiveMaximum'] ) ) {
                    if ( $value >= $args['maximum'] || $value <= $args['minimum'] ) {
                        return new WP_Error( 'rest_invalid_param', sprintf( __( '%s must be between %d (exclusive) and %d (exclusive)' ), $param, $args['minimum'], $args['maximum'] ) );
                    }
                } else if ( empty( $args['exclusiveMinimum'] ) && ! empty( $args['exclusiveMaximum'] ) ) {
                    if ( $value >= $args['maximum'] || $value < $args['minimum'] ) {
                        return new WP_Error( 'rest_invalid_param', sprintf( __( '%s must be between %d (inclusive) and %d (exclusive)' ), $param, $args['minimum'], $args['maximum'] ) );
                    }
                } else if ( ! empty( $args['exclusiveMinimum'] ) && empty( $args['exclusiveMaximum'] ) ) {
                    if ( $value > $args['maximum'] || $value <= $args['minimum'] ) {
                        return new WP_Error( 'rest_invalid_param', sprintf( __( '%s must be between %d (exclusive) and %d (inclusive)' ), $param, $args['minimum'], $args['maximum'] ) );
                    }
                } else if ( empty( $args['exclusiveMinimum'] ) && empty( $args['exclusiveMaximum'] ) ) {
                    if ( $value > $args['maximum'] || $value < $args['minimum'] ) {
                        return new WP_Error( 'rest_invalid_param', sprintf( __( '%s must be between %d (inclusive) and %d (inclusive)' ), $param, $args['minimum'], $args['maximum'] ) );
                    }
                }
            }
        }

        return true;
    }
}

if ( ! function_exists( 'rest_sanitize_request_arg' ) ) {
    /**
     * Sanitize a request argument based on details registered to the route.
     *
     * @param  mixed            $value
     * @param  CUTV_REST_Request  $request
     * @param  string           $param
     * @return mixed
     */
    function rest_sanitize_request_arg( $value, $request, $param ) {

        $attributes = $request->get_attributes();
        if ( ! isset( $attributes['args'][ $param ] ) || ! is_array( $attributes['args'][ $param ] ) ) {
            return $value;
        }
        $args = $attributes['args'][ $param ];

        if ( 'integer' === $args['type'] ) {
            return (int) $value;
        }

        if ( isset( $args['format'] ) ) {
            switch ( $args['format'] ) {
                case 'date-time' :
                    return sanitize_text_field( $value );

                case 'email' :
                    /*
                     * sanitize_email() validates, which would be unexpected
                     */
                    return sanitize_text_field( $value );

                case 'uri' :
                    return esc_url_raw( $value );
            }
        }

        return $value;
    }

}

global $wpdb;
define( 'CUTV_MAIN_FILE' , __FILE__ );
define ( 'HDFLVVIDEOSHARE', $wpdb->prefix . 'hdflvvideoshare' );
define ( 'WVG_PLAYLIST', $wpdb->prefix . 'hdflvvideoshare_playlist' );
define ( 'WVG_MED2PLAY', $wpdb->prefix . 'hdflvvideoshare_med2play' );

/* Including functions definitions */
require_once( 'cutv.definitions.php' );
require_once( 'cutv.hooks.php' );
require_once( 'cutv.functions.php' );
/* Including Sources CPT definitions */
//require_once( 'includes/cutv.sources.php' );


function cutv_add_channel() {

    // The $_REQUEST contains all the data sent via ajax
    if ( isset($_REQUEST) ) {
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

//        $playlists = $wpdb->get_results( 'SELECT * FROM ' . WVG_PLAYLIST );

        $query = $wpdb->prepare("INSERT INTO " . WVG_PLAYLIST . " (pid, playlist_name, playlist_slugname, playlist_desc, is_publish, playlist_order) VALUES ( %d, %s, %s, %s, %d, %d )",
            array($cat_id, $channelName, $slug, $description, 1, count($playlists))
        );
        $wpdb->query( $query);


        $playlistsUpdated = $wpdb->get_results( 'SELECT * FROM ' . WVG_PLAYLIST );

        // Now we'll return it to the javascript function
        // Anything outputted will be returned in the response
        echo print_r($playlistsUpdated);

        // If you're debugging, it might be useful to see what was sent in the $_REQUEST
        // print_r($_REQUEST);

    }

    // Always die in functions echoing ajax content
    die();
}
add_action( 'wp_ajax_cutv_add_channel', 'cutv_add_channel' );


function cutv_convert_snaptube() {

    echo 'test';
    // The $_REQUEST contains all the data sent via ajax
    if ( isset($_REQUEST) ) {
        global $wpdb;

        $videos = $wpdb->get_results('SELECT * FROM ' . HDFLVVIDEOSHARE);

        $cat_id         = $_REQUEST['cat_id'];

        $description    = $_REQUEST['description'];
        $name           = $_REQUEST['name'];
        $description    = $_REQUEST['description'];
        $file           = $_REQUEST['file'];
        $slug           = $_REQUEST['slug'];
        $duration       = $_REQUEST['duration'];
        $image          = $_REQUEST['image'];
        $opimage        = $_REQUEST['opimage'];
        $post_date      = $_REQUEST['post_date'];
        $link           = $_REQUEST['link'];
        $member_id      = $_REQUEST['member_id'];

        $download       = 0;
        $featured       = 1;
        $publish        = 1;
        $file_type      = 1;
        $islive         = 0;
        $vid            = count($videos) + 1;
        $ordering       = $vid;
        $amazon_bucket  = 0;
        $slug           = $_REQUEST['slug'];

//
//        // INSERT INTO HDFLVVIDEOSHARE TABLE
//        // @this is the snaptube video gallery table
//        $query_vids = $wpdb->prepare("INSERT INTO " . HDFLVVIDEOSHARE . " (vid, name, description, file, slug, file_type, duration, image, opimage, download, link, featured, post_date, publish, islive, member_id, ordering, amazon_bucket) VALUES ( %d, %s, %s, %d, %s, %s, %d, %s, %s, %d, %s, %s, %d, %s, %s, %d, %s, %s )",
//            array($vid, $name, $description, $file, $slug, $file_type, $duration, $image, $opimage, $download, $link, $featured, $post_date, $publish, $islive, $member_id, $ordering, $amazon_bucket)
//        );
//        $wpdb->query($query_vids);
//        $video_gallery = $wpdb->get_results('SELECT * FROM ' . HDFLVVIDEOSHARE . ' WHERE post_type="videogallery"');

        // INSERT INTO MED2PLAY TABLE
        // @this is the category table sort of
        foreach ($cat_id as $value) {

            $med2play = $wpdb->get_results('SELECT * FROM ' . WVG_MED2PLAY);
            $query_med2play = $wpdb->prepare("INSERT INTO " . WVG_MED2PLAY . " (rel_id, media_id, playlist_id) VALUES ( %d, %s, %s )",
                array(count($med2play), $vid, $value)
            );
            $wpdb->query($query_med2play);

        }

        $videos = $wpdb->get_results('SELECT * FROM ' . HDFLVVIDEOSHARE);


        // Now we'll return it to the javascript function
        // Anything outputted will be returned in the response
//        echo print_r($video_gallery);
        echo print_r($playlistsUpdated);

        // If you're debugging, it might be useful to see what was sent in the $_REQUEST
        // print_r($_REQUEST);



    }

    // Always die in functions echoing ajax content
    die();
}
add_action( 'wp_ajax_cutv_convert_snaptube', 'cutv_convert_snaptube' );

function cutv_snaptube_playlists() {
    global $wpdb;

    $playlists = $wpdb->get_results( 'SELECT * FROM ' . WVG_PLAYLIST);
//    print_r($playlists);
    foreach($playlists as $obj) {
        $args = array(
            'posts_per_page'   => -1,
            'category'         => $obj->pid,
//            'category_name'    => '',
            'orderby'          => 'playlist_order',
            'order'            => 'DESC',
            'post_type'        => 'wpvr_video',
            'post_status'      => 'publish',
        );
        $posts_array = get_posts( $args );
        //echo  "<h3>$obj->pid - $obj->playlist_name (". count($posts_array) .")</h3>";

//        print_r($posts_array);
        foreach ( $posts_array as $post ) {
            $cats = get_the_category( $post->ID );

            foreach ( $cats as $cat ) {
                $the_category = $cat;
                //echo "[$post->ID] $post->post_title | category: $cat->term_id<br>";
            }

        }
        //echo "<hr>";
    }
//    exit;



//    $query = $wpdb->prepare("INSERT INTO " . WVG_PLAYLIST . " (pid, playlist_name, playlist_slugname, playlist_desc, is_publish, playlist_order) VALUES ( %d, %s, %s, %s, %d, %d )",
//        array($cat_id, $channelName, $slug, $description, 1, count($playlists))
//    );
//    $wpdb->query( $query);
//
//
//    $playlistsUpdated = $wpdb->get_results( 'SELECT * FROM ' . WVG_PLAYLIST );

    // Now we'll return it to the javascript function
    // Anything outputted will be returned in the response
//    echo 'boom';



}
add_action('init','cutv_snaptube_playlists');

//create a function that will attach our new 'channel' taxonomy to the 'post' post type
function add_channel_taxonomy_to_post(){

    //set the name of the taxonomy
    $taxonomy = 'channel';
    //set the post types for the taxonomy
    $object_type = 'page';

    //populate our array of names for our taxonomy
    $labels = array(
        'name'               => 'Channels',
        'singular_name'      => 'Channel',
        'search_items'       => 'Search Channels',
        'all_items'          => 'All Channels',
        'parent_item'        => 'Parent Channel',
        'parent_item_colon'  => 'Parent Channel:',
        'update_item'        => 'Update Channel',
        'edit_item'          => 'Edit Channel',
        'add_new_item'       => 'Add New Channel',
        'new_item_name'      => 'New Channel Name',
        'menu_name'          => 'Channel'
    );

    //define arguments to be used
    $args = array(
        'labels'            => $labels,
        'hierarchical'      => true,
        'show_ui'           => true,
        'how_in_nav_menus'  => true,
        'public'            => true,
        'show_admin_column' => true,
        'query_var'         => true,
        'rewrite'           => array('slug' => 'channel')
    );

    //call the register_taxonomy function
    register_taxonomy($taxonomy, $object_type, $args);
}
add_action('init','add_channel_taxonomy_to_post');
// If you wanted to also use the function for non-logged in users (in a theme for example)
// add_action( 'wp_ajax_nopriv_cutv_add_channel', 'cutv_add_channel' );

// Load plugin class files
require_once( 'includes/class-wordpress-plugin-template.php' );
require_once( 'includes/class-wordpress-plugin-template-settings.php' );

// Load plugin libraries
require_once( 'includes/lib/class-wordpress-plugin-template-admin-api.php' );
require_once( 'includes/lib/class-wordpress-plugin-template-post-type.php' );
require_once( 'includes/lib/class-wordpress-plugin-template-taxonomy.php' );


/**
 * Returns the main instance of CUTV_Channel to prevent the need to use globals.
 *
 * @since  0.0.1
 * @return object CUTV_Channel
 */
function CUTV_Channel () {
    $instance = CUTV_Channel::instance( __FILE__, '1.0.0' );

    if ( is_null( $instance->settings ) ) {
        $instance->settings = CUTV_Channel_Settings::instance( $instance );
    }

    return $instance;
}

CUTV_Channel();