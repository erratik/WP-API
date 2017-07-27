'use strict';

/**
 * @ngdoc directive
 * @name cutvApiAdminApp.directive:manageSources
 * @description
 * # manageChannel
 */
angular.module('cutvApiAdminApp')
    .directive('manageSources', function ($templateRequest, $http, $compile, ChannelService) {
        return {
            restrict: 'E',
            replace: true,
            scope: {
                channel: '='
            },
            template: '',
            link: function (scope, element, attrs) {

                scope.query = '';
                scope.updateSuccess = false;
                scope.channelId = scope.channel.pid;

                $templateRequest('/wp-content/plugins/cutv-api/app/templates/manage-sources.html').then(function(html){

                    var template = angular.element(html);
                    element.html(template);
                    $compile(template)(scope);


                });

                scope.sourcesSelected = function (source) {
                    return _.filter(scope.sources, 'selected');
                };

                scope.updateSource = function (source, channelId) {
                    source.selected = !source.selected;
                };

                scope.updateChannel = function(moveVideos) {

                    var data = {
                        action: 'cutv_update_source_categories',
                        sources: JSON.stringify(_.map(_.filter(scope.sources, 'selected'), function(o){ return o.ID })), //,
                        channel: scope.channel.pid,
                        move_videos: moveVideos
                    };


                    return $http.get(ajaxurl , {params: data}).then(function(res) {

                        res = res.data;
                        scope.channel.source_count = res.length;

                        makeSourceObj(res);

                        scope.updateSuccess = true;

                    });

                };


                function makeSourceObj(updatingSources) {

                    var channel_id = scope.channelId;
                    scope.channel['sources'] = scope.sources.filter((src) => { return src.selected });

                }


                (function initialize() {

                    ChannelService.getSources(scope.channelId).then((sources) => {
                        scope.sources = sources;

                        // debugger;
                        scope.channel = cutv.channels.filter(c => {
                            // c.enabled = c.enabled === 'true' ? true : false;
                        // acking it becase of semantic sucking
                            _.forEach(c, function(value, key){
                                var convertedValue = value == 'false' ? false : value;
                                    convertedValue = value == 'true' ? 1 : value;
                                    convertedValue = !_.isNaN(Number(value)) ? Number(value) : value;

                                if ( key == 'enabled') {
                                    convertedValue = value != 'false' && value != '' ? false : true;
                                }

                                c[key] = convertedValue;

                            });
                            c.enabled  = !c.enabled;
                            return c.pid == scope.channelId;
                        })[0];

                        // scope.sources = sources;
                        makeSourceObj();
                    });
                })();

            }
        };
    });
