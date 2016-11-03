<?php
/**
 * Page template file.
 */
get_header();

$img = wp_get_attachment_image_src( get_post_thumbnail_id(), 'large-image' );

if ( LAYOUT == 'sidebar-no' ) {
    $span_size = 'span10';
} else {
    $span_size = '';
}

?>
    <div class="page-<?php echo LAYOUT; ?> page-wrapper">
        <div class="clearfix"></div>
        <div class="content vc_row wpb_row vc_row-fluid">
            <?php
            wp_reset_postdata();
            $suggested_videos = get_option('vh_suggested_videos') ? get_option('vh_suggested_videos') : '';
            ?>
            <div class="cutv-channels">
            </div>
            <?php
            vh_get_sidebar_menu('true');
//            vh_get_suggested_videos($suggested_videos);
            ?>
            <div class="<?php echo LAYOUT; ?>-pull">
                <div class="main-content vc_col-sm-10">
                    <?php
                    if ( !is_front_page() && !is_home() ) { ?>
                        <div class="page-title">
                            <?php echo  the_title( '<h1>', '</h1>' ); ?>
                        </div>
                    <?php } ?>
                    <?php
                    if ( !is_front_page() && !is_home() ) {
//                        echo the_breadcrumbs();
                    } ?>
                    <?php
                    if ( isset($img[0]) ) { ?>
                        <div class="entry-image">
                            <img src="<?php echo $img[0]; ?>" class="open_entry_image <?php echo $span_size; ?>" alt="" />
                        </div>
                    <?php } ?>
                    <div class="main-inner">
                        <?php
                        $args = array(
                            'numberposts' => -1,
                            'post_type'   => 'catablog-items'
                        );

                        $posts = get_posts($args);

                        $categories = get_categories( array('taxonomy' => 'catablog-terms') ) ;

                        foreach ($categories as $category) {

                            echo "<h2 style=\"color: white;\">$category->name</h2>";

                            foreach ($posts as $post) {

                                $item_cat = get_the_catalog_cat($post->ID);
                                if ($post->post_type == $args['post_type'] && $item_cat[0]->name == $category->name) {

                                    $image = '/wp-content/uploads/catablog/thumbnails/'.get_post_meta($post->ID, 'catablog-post-meta', true )['image'];
                                    ?>

                                    <a href="/?p=<?php echo $post->ID; ?>">

                                        <div class="flex catalog-item">
                                            <div class="image">
                                                <img src="<?php echo $image; ?>" alt="">
                                            </div>
                                            <div class="description">
                                                <h3><?php echo $post->post_title; ?></h3>
                                                <!--<div class="content"><?php echo $post->post_content; ?></div>-->
                                            </div>
                                        </div>
                                    </a>

                                    <?php
                                }

                            }

                        }

                        ?>
                    </div>
                </div>
            </div>
            <?php
            if (LAYOUT == 'sidebar-right') {
                ?>
                <div class="vc_col-sm-3 pull-right <?php echo LAYOUT; ?>">
                    <div class="sidebar-inner">
                        <?php
                        global $vh_is_in_sidebar;
                        $vh_is_in_sidebar = true;
                        generated_dynamic_sidebar();
                        ?>
                        <div class="clearfix"></div>
                    </div>
                </div><!--end of span3-->
            <?php } ?>
            <?php $vh_is_in_sidebar = false; ?>
            <div class="clearfix"></div>
        </div><!--end of content-->
        <div class="clearfix"></div>
    </div><!--end of page-wrapper-->
    <script id="cutv-channels" type="handlebars/template">
        <ul>
            {{#each this}}
            {{#if this.enabled}}
            <li>
                <a href="/channels/?playid={{this.pid}}">
                    {{#if this.cutv_channel_img }}<img src="/wp-content/uploads/channels/{{this.cutv_channel_img}}">
                    {{^}}<img src="https://placeholdit.imgix.net/~text?txtsize=33&txt={{this.playlist_name}}&w=200&h=200">
                    {{/if}}
                </a>
            </li>
            {{/if}}

            {{/each}}
        </ul>
    </script>
<?php get_footer();
?>
