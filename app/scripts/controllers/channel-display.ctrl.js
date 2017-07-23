'use strict';

/**
 * @ngdoc function
 * @name cutvApiAdminApp.controller:ChannelDisplayCtrl
 * @description
 * # MainCtrl
 * Controller of the cutvApiAdminApp
 */
angular.module('cutvApiAdminApp')

    .controller('ChannelDisplayCtrl', function ($scope, $http, $location, $routeParams, ChannelService) {


        ChannelService.getChannels($routeParams.channelId).then(channel => $scope.channel = channel);

        console.log('test');
        // todo: update .spec for deleteChannel()
        $scope.updateVisibility = function(moveVideos) {
            // acking it becase of semantic sucking
            $scope.channel.enabled  = !$scope.channel.enabled;
            var data = {
                action: 'cutv_update_source_categories',
                channel: $scope.channel.pid
            };

        };



    });
