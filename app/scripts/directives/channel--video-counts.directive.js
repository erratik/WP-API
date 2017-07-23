'use strict';

/**
 * @ngdoc directive
 * @name cutvApiAdminApp.directive:videoCounts
 * @description
 * # manageChannel
 */
angular.module('cutvApiAdminApp')
    .directive('videoCounts', function ($http, $compile, ChannelService) {
        return {
            restrict: 'E',
            replace: true,
            scope: {
                channel: '=',
                display: '@'
            },
            templateUrl: '/wp-content/plugins/cutv-api/app/templates/directives/channel--video-counts.html',
            link: function (scope, element, attrs) {


            }
        };
    });
