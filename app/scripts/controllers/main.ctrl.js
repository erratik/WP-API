'use strict';

/**
 * @ngdoc function
 * @name cutvApiAdminApp.controller:MainCtrl
 * @description
 * # MainCtrl
 * Controller of the cutvApiAdminApp
 */
angular.module('cutvApiAdminApp')

.controller('MainCtrl', function($scope, $http, $location, ChannelService) {

    $scope.init = () => {

        var data = {
            count: 1,
            exclude_sources: 0,
            action: 'cutv_get_channels',
        };

        ChannelService.handlePluginAction(data)
            .then(res => {
                $scope.channels = res.map(({
                    channel,
                    sources
                }) => ({
                    ...channel,
                    sources,
                    isLoading: true
                }));
                return res.map(x => x.sources);
            })
            .then(() => $scope.channels.map(channel => channel.counts = ['publish', 'draft', 'pending'].map(status => {
                const count = {};
                count[status] = Array.prototype.concat.apply([], channel.sources.map(x => x.videos[status])).reduce((a, b) => a + b, 0);
                return count;
            }))).finally(() => $scope.channels = $scope.channels.map(channel => ({
                ...channel,
                isLoading: false
            })));

    };

    $scope.init();

    $scope.newChannel = {
        name: null,
        enabled: false,
        sources: []
    };

    $scope.addChannel = function() {
        var slug = $scope.newChannel.name.toLowerCase().replace(/ /g, '-');
        var createChannelRequest = {
            'action': 'cutv_add_channel',
            channelName: $scope.newChannel.name,
            enabled: $scope.newChannel.enabled,
            featured: $scope.newChannel.featured,
            slug: slug
        };

        return $http.post(ajaxurl, createChannelRequest).then(function(addedCategory) {
            $scope.channels.unshift(addedCategory.data);
        });

    };

    $scope.openAddChannelDialog = () => {

        $('#addChannel').modal('show');

    };

});