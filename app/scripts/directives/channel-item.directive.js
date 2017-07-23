'use strict';

/**
 * @ngdoc directive
 * @name cutvApiAdminApp.directive:channelItem
 * @description
 * # manageChannel
 */
angular.module('cutvApiAdminApp')
    .directive('channelItem', function ($templateRequest, $http, $compile, ChannelService) {
        return {
            restrict: 'E',
            replace: true,
            scope: {
                channel: '=',
                sources: '='
            },
            template: '<div><div class="ui active inverted dimmer"><div class="ui text loader">LOADING CHANNELS</div></div></div>',
            link: function (scope, element, attrs) {


                ChannelService.getChannelSources(scope.channel.pid).then((sources) => {
                    scope.sources = sources;
                    ChannelService.countSourceVideos(scope);

                    $templateRequest('/wp-content/plugins/cutv-api/app/templates/channel-item.html').then(function(html){

                        var template = angular.element(html);
                        element.html(template);
                        $compile(template)(scope);

                    });
                });




            }
        };
    });
