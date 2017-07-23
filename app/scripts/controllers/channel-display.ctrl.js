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

        $scope.updateChannel = function() {

            var query = {
                action: 'cutv_update_channel',
                channel: $scope.channel.pid,
                name: $scope.channel.playlist_name,
                enabled: $scope.channel.enabled,
                image: ''
            };

            ChannelService.wpRequest(query).then(channel => $scope.channel = channel);

        };

    });
