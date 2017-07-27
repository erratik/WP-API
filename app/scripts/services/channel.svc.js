'use strict';

/**
 * @ngdoc function
 * @name cutvApiAdminApp.controller:MainCtrl
 * @description
 * # MainCtrl
 * Controller of the cutvApiAdminApp
 */
angular.module('cutvApiAdminApp')

    .service('ChannelService', function ($http, $timeout) {
        var ChannelService = {};

        ChannelService.getChannel = function(channelId) {
            return $http.get(`/wp-admin/admin-ajax.php?action=cutv_get_channel&channel_id=${channelId}`).then(res => res.data);
        };

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


        ChannelService.updateChannel = function($scope, update = true) {


            var query = {
                action: 'cutv_update_channel',
                channel: $scope.channel.pid,
                name: $scope.channel.playlist_name,
                enabled: $scope.channel.enabled,
                featured: $scope.channel.featured,
                image: $scope.channel.uploadedImage || $scope.channel.cutv_channel_img
            };

            ChannelService.wpRequest(query).then(channel => {
                if (update) {
                    $scope.channel = channel;
                }

                $scope.$emit('channelUpdated');

                $scope.updateSuccess = true;
                $timeout(() => $scope.updateSuccess = false, 2000);
            });


        };

        ChannelService.countSourceVideos = function($scope, cb = null) {

            $scope.channel.counts = {};
            if ($scope.sources.length) {

                $scope.sources = $scope.sources.map(source => {

                    Object.keys(source.source_video_counts).forEach(status => {
                        source.source_video_counts[status] = !source.source_video_counts[status] ? 0 : source.source_video_counts[status].length;
                    })

                    return source;
                });

                $scope.sources.forEach(source => {
                    Object.keys(source.source_video_counts).forEach(status => {
                        $scope.channel.counts[status] = _.sumBy($scope.sources, function(o) { return o.source_video_counts[status]; });
                    });
                });

            }
                            $scope.channel.isLoading = false;


        };



        var toQueryString = function(obj) {
            return _.map(obj,function(v,k){
                return encodeURIComponent(k) + '=' + encodeURIComponent(v);
            }).join('&');
        };
        return ChannelService;
    });

