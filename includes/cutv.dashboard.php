<?php


	global $wpvr_colors , $wpvr_status , $wpvr_services , $wpvr_types_;
	global $wpvr_vs;
	//$max_wanted_videos = wpvr_max_fetched_videos_per_run();
	wp_localize_script( 'cutv-api', 'wpApiSettings', array( 'root' => esc_url_raw( rest_url() ), 'nonce' => wp_create_nonce( 'wp_rest' ) ) );
	wp_enqueue_script( 'cutv-api' );
?>


<div class="ui">
    <h2 class="ui dividing header">Site</h2>
    <?php
        // get all the catgeories
        print_r(get_terms())   ;
    ?>
    </div>
    <div class="ui three column stackable grid">

        <?php $sources_stats = wpvr_sources_stats( $group = TRUE ); ?>
        <?php $video_stats = wpvr_videos_stats(); ?>

        <?php
        global $is_DT;
        $is_DT = TRUE;
        //$sources_stats = wpvr_sources_stats( $group = true );
        //$video_stats = wpvr_videos_stats(  );

        //_d( $sources_stats );

        //_d( $video_stats);

        $new_video_link  = CUTV_SITE_URL . '/wp-admin/post-new.php?post_type=' . CUTV_VIDEO_TYPE;
        $new_source_link = CUTV_SITE_URL . '/wp-admin/post-new.php?post_type=' . CUTV_SOURCE_TYPE;
        ?>


        <div class="column">
            <!-- VIDEOS WIDGET -->
            <div id = "" class = "postbox ">
                <h3 class = "hndle"><span> <?php _e( 'YOUR VIDEOS' , CUTV_LANG ); ?> </span></h3>

                <div class = "inside">
                    <div>
                        <div class = "wpvr_graph_wrapper" style = "width:100% !important; height:400px !important;">
                            <div class = "wpvr_graph_fact">
                                <?php if( $video_stats != FALSE ) { ?>
                                    <span><?php echo wpvr_numberK( $video_stats[ 'byStatus' ][ 'total' ] ); ?></span><br/>
                                    <?php _e( 'videos' , CUTV_LANG ); ?>
                                <?php } else { ?>
                                    <div class = "wpvr_message">
                                        <i class = "fa fa-frown-o"></i><br/>
                                        <?php _e( 'There is no video.' , CUTV_LANG ); ?>
                                    </div>
                                    <p>
                                        <a href = "<?php echo $new_video_link; ?>" class = "wpvr_black_button wpvr_submit_button wpvr_graph_button">
                                            <i class = "fa fa-plus"></i>
                                            <?php _e( 'Import your first video.' , CUTV_LANG ); ?>
                                        </a>
                                    </p>
                                <?php } ?>
                            </div>
                            <canvas id = "wpvr_chart_videos_by_status" width = "900" height = "400"></canvas>
                        </div>
                        <?php if( count( $video_stats[ 'byStatus' ][ 'items' ] ) != 0 ) { ?>
                            <script>
                                var data_videos_by_status = [
                                    <?php foreach( (array) $video_stats[ 'byStatus' ][ 'items' ] as $label=>$count){ ?>
                                    <?php if( $label == 'total' ) continue; ?>

                                    {
                                        value: parseInt(<?php echo $count; ?>),
                                        color: '<?php echo $wpvr_status[ $label ][ 'color' ]; ?>',
                                        label: '<?php echo strtoupper( $wpvr_status[ $label ][ 'label' ] ); ?>',
                                    },
                                    <?php } ?>
                                ];
                                jQuery(document).ready(function ($) {
                                    wpvr_draw_chart(
                                        $('#wpvr_chart_videos_by_status'),
                                        $('#wpvr_chart_videos_by_status_legend'),
                                        data_videos_by_status,
                                        'donut'
                                    );
                                });
                            </script>
                        <?php } ?>
                    </div>
                    <?php if( $sources_stats[ 'total' ] != 0 ) { ?>
                        <div class = "wpvr_widget_legend">
                            <div id = "wpvr_chart_videos_by_status_legend"></div>
                        </div>
                    <?php } ?>
                    <div class = "wpvr_clearfix"></div>
                </div>
            </div>
            <!-- VIDEOS WIDGET -->
        </div>
        <div class="column">
            <!-- FILTER BY CAT -->
            <?php $fcb_categories = cutv_manage_render_filters( 'categories' ); ?>
            <?php if( $fcb_categories ) { ?>
                <div class = "wpvr_manage_box open">
                    <div class = "wpvr_manage_box_head">
                        <i class = " fa fa-folder-open"></i>
                        <?php _e( 'Filter by' , CUTV_LANG ); ?> <?php _e( 'Categories' , CUTV_LANG ); ?>
                        <i class = "pull-right caretDown fa fa-caret-down"></i>
                        <i class = "pull-right caretUp fa fa-caret-up"></i>
                    </div>
                    <div class = "">
                        <?php echo $fcb_categories; ?>
                    </div>
                </div>
            <?php } ?>
            <!-- FILTER BY CAT -->
            <!--<h2>Example body text</h2>
            <p>Nullam quis risus eget <a href="#">urna mollis ornare</a> vel eu leo. Cum sociis natoque penatibus et magnis dis parturient montes, nascetur ridiculus mus. Nullam id dolor id nibh ultricies vehicula.</p>
            <p><small>This line of text is meant to be treated as fine print.</small></p>
            <p>The following snippet of text is <strong>rendered as bold text</strong>.</p>
            <p>The following snippet of text is <em>rendered as italicized text</em>.</p>
            <p>An abbreviation of the word attribute is <abbr title="attribute">attr</abbr>.</p>-->
        </div>
        <div class="column">
            <!--<div class="ui three column stackable padded middle aligned centered color grid">
                <div class="red column">Red</div>
                <div class="orange column">Orange</div>
                <div class="yellow column">Yellow</div>
                <div class="olive column">Olive</div>
                <div class="green column">Green</div>
                <div class="teal column">Teal</div>
                <div class="blue column">Blue</div>
                <div class="violet column">Violet</div>
                <div class="purple column">Purple</div>
                <div class="pink column">Pink</div>
                <div class="brown column">Brown</div>
                <div class="grey column">Grey</div>
                <div class="black column">Black</div>
            </div>-->
        </div>
    </div>
</div>

