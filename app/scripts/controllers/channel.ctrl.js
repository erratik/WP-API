'use strict';

/**
 * @ngdoc function
 * @name cutvApiAdminApp.controller:ChannelCtrl
 * @description
 * # MainCtrl
 * Controller of the cutvApiAdminApp
 */
angular.module('cutvApiAdminApp')

    .controller('ChannelCtrl', function ($scope, $http, $location, $routeParams, ChannelService) {

        // init
        $scope.channelName = $routeParams.channelName;

        ChannelService.getChannels($routeParams.channelId).then(channel => {
            $scope.channel = channel;
            console.log(channel);

                $scope.channel.counts = {};

            return channel;
        }).then(channel => {
            ChannelService.getChannelSources(channel.pid).then((sources) => {
                $scope.sources = sources;
                ChannelService.countSourceVideos($scope);

                $scope.activeTab = !sources.length ? 'sources' : 'videos';
            });
        });

        // utils
        $scope.selectAction = () => {
            console.log($scope.selectedOption);
        };


    });
