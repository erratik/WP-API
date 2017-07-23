'use strict';

/**
 * @ngdoc function
 * @name cutvApiAdminApp.controller:MainCtrl
 * @description
 * # MainCtrl
 * Controller of the cutvApiAdminApp
 */
angular.module('cutvApiAdminApp')

    .service('ChannelService', function ($http) {
        var ChannelService = {};
        ChannelService.getChannels = function(channelId = null) {
            return $http.get('/wp-admin/admin-ajax.php?action=cutv_get_channels&json=true').then(function (res) {
                // console.log(res.data.filter(c => c.pid === channelId));
                return channelId ? res.data.filter(c => c.pid === channelId)[0] : res.data;
            });
        };

        ChannelService.getChannelSources = function(channelId) {
            return $http.get(`/wp-admin/admin-ajax.php?action=cutv_get_sources_by_channel&channel_id=${channelId}&json=true`).then(function (res) {
                return res.data;
            });
        };

        ChannelService.getSources = function(channelId) {
            return $http.get(`/wp-admin/admin-ajax.php?action=cutv_get_sources_info&channel_id=${channelId}&json=true`).then(function (res) {
                return res.data;
            });
        };


        ChannelService.moveSourceVideos = function(currentSrc, newSrc, movePlaylists) {
            return $http.get(`/wp-admin/admin-ajax.php?action=cutv_move_source_videos&currentSrc=${currentSrc}&newSrc=${newSrc}&movePlaylists=${movePlaylists}`).then(function (res) {
                return res.data;
            });
        };

        ChannelService.getSourceVideos = function(sourceId) {
            return $http.get(`/wp-admin/admin-ajax.php?action=cutv_get_source_video_posts&source_id=${sourceId}&json=true`).then(function (res) {
                return res.data;
            });
        };

        ChannelService.wpRequest = function(query) {
            return $http.get(`/wp-admin/admin-ajax.php?${toQueryString(query)}&json=true`).then(function (res) {
                return res.data;
            });
        };


        ChannelService.countSourceVideos = function($scope) {

            $scope.channel.counts = {};
            $scope.sources = $scope.sources.map(source => {

                Object.keys(source.source_video_counts).forEach(status => {
                    source.source_video_counts[status] = source.source_video_counts[status].length;
                })

                return source;
            });

            $scope.sources.forEach(source => {
                Object.keys(source.source_video_counts).forEach(status => {
                    $scope.channel.counts[status] = _.sumBy($scope.sources, function(o) { return o.source_video_counts[status]; });
                });
            });

        };

        var toQueryString = function(obj) {
            return _.map(obj,function(v,k){
                return encodeURIComponent(k) + '=' + encodeURIComponent(v);
            }).join('&');
        };
        return ChannelService;
    });

