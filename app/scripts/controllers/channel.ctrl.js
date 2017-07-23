'use strict';

/**
 * @ngdoc function
 * @name cutvApiAdminApp.controller:ChannelCtrl
 * @description
 * # MainCtrl
 * Controller of the cutvApiAdminApp
 */
angular.module('cutvApiAdminApp')

.controller('ChannelCtrl', function($scope, $http, $location, $routeParams, ChannelService) {


    ChannelService.getChannels($routeParams.channelId).then(channel => {
        $scope.channel = channel;
        $scope.channel.counts = {};
        return channel;
    }).then(channel => {
        ChannelService.getChannelSources(channel.pid).then((sources) => {
            $scope.sources = sources;
            ChannelService.countSourceVideos($scope);
            $scope.activeTab = !sources.length ? 'sources' : 'videos';
        });
    });

    $scope.$on('channelImageUpdated', (e) => $scope.channel.cutv_channel_img = e.targetScope.filename);
    $scope.$on('channelUpdated', (e) => $scope.channel = e.targetScope.channel);

});