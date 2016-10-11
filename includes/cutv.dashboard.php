<?php


    global $wpvr_colors, $wpvr_status, $wpvr_services, $wpvr_types_;
    global $wpvr_vs;
    //$max_wanted_videos = wpvr_max_fetched_videos_per_run();
    wp_localize_script('cutv-api', 'wpApiSettings', array('root' => esc_url_raw(rest_url()), 'nonce' => wp_create_nonce('wp_rest')));
    wp_enqueue_script('cutv-api');
    $cutv_channels = cutv_get_channels();
    $all_sources = cutv_get_sources_info(true);
?>
<script>

    var cutv = {
        channels: <?php echo json_encode($cutv_channels); ?>
    };

    function makeSourceObj(sources) {

        if ( typeof sources == 'string') sources = JSON.parse(sources);

        _.forEach(cutv.channels, function(channel){

            var channel_id = channel.pid;

            channel['sources'] = [];
            channel['counts'] = {
                unpublished : 0,
                published   : 0
            };

            _.forEach(sources['assigned'], function(source) {
                _.findIndex(source.categories, function(o) {
                    if (o == channel_id) {

                        source.selected = true;
                        channel['sources'].push(source);

                        channel['counts']['unpublished'] =  channel['counts']['unpublished']+Number(source['videos']["unpublished_count"]);
                        channel['counts']['published'] =  channel['counts']['published']+Number(source['videos']['published_count']) ;

                    }
                });
            });
            channel.source_count = channel['sources'].length;


            _.forEach(sources['unassigned'], function(source){
                channel['sources'].push(source);
                source.selected = false;
            });

        });
    }
    var sources = <?php echo json_encode($all_sources); ?>

    makeSourceObj(sources);

</script>

    <div>
        <div ng-view=""></div>
    </div>


