'use strict';

/**
 * @ngdoc function
 * @name cutvApiAdminApp.controller:MainCtrl
 * @description
 * # MainCtrl
 * Controller of the cutvApiAdminApp
 */
angular.module('cutvApiAdminApp')

    .controller('MainCtrl', function ($scope, $http, ChannelService) {

        if (typeof cutv == 'undefined') {
            ChannelService.getChannels().then(function (data) {
                $scope.channels = data
            });
        } else {
            $scope.channels = cutv.channels;
            $scope.sources = sources;
        }


        // todo: update .spec for addChannel()
        $scope.newChannel = {
            name: null,
            enabled: false,
            sources: []
        };

        $scope.addChannel = function() {
            var slug = $scope.newChannel.name.toLowerCase().replace(/ /g, '-');
            var createChannelRequest = {
                'action' : 'cutv_add_channel',
                channelName: $scope.newChannel.name,
                enabled: $scope.newChannel.enabled,
                slug: slug
            };


            return $http.post(ajaxurl, createChannelRequest).then(function(addedCategory) {

                $scope.channels.unshift(addedCategory.data);

                console.log($scope.channels);



            });

        };

    })
    .service('ChannelService', function ($http) {
        var ChannelService = {};
        ChannelService.getChannels = function() {
            return $http.get('http://cutv.dev/wp-admin/admin-ajax.php?action=cutv_get_channels&json=true').then(function (res) {
                // console.log(res);
                return res.data;
            });
        };

        return ChannelService;
    });

