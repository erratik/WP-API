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
//            vh_get_sidebar_menu('true');
//            vh_get_suggested_videos($suggested_videos);
            ?>
            <div class="<?php echo LAYOUT; ?>-pull">
                <div class="main-content vc_col-sm-12">
                    <?php
                    if ( !is_front_page() && !is_home() ) { ?>
                        <div class="page-title">
                            <?php echo  the_title( '<h1>', '</h1>' ); ?>
                        </div>
                    <?php } ?>
                    <?php
                    if ( !is_front_page() && !is_home() ) {
                        echo vh_breadcrumbs();
                    } ?>
                    <?php
                    if ( isset($img[0]) ) { ?>
                        <div class="entry-image">
                            <img src="<?php echo $img[0]; ?>" class="open_entry_image <?php echo $span_size; ?>" alt="" />
                        </div>
                    <?php } ?>
                    <div class="main-inner">
                        <?php
                        if (have_posts ()) {
                            while (have_posts()) {
                                the_post();
                                the_content();
                            }
                        } else {
                            echo '
							<h2>Nothing Found</h2>
							<p>Sorry, it appears there is no content in this section.</p>';
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
        <li>
            <a href="/channels/?playid={{this.pid}}">
                {{#if this.cutv_channel_img }}<img src="/wp-content/uploads/channels/{{this.cutv_channel_img}}">
                {{^}}<img src="https://placeholdit.imgix.net/~text?txtsize=33&txt={{this.playlist_name}}&w=200&h=200">
                {{/if}}
            </a>
        </li>
    {{/each}}
    </ul>
</script>
<?php get_footer();