'use strict';

/**
 * @ngdoc function
 * @name cutvApiAdminApp.controller:MainCtrl
 * @description
 * # MainCtrl
 * Controller of the cutvApiAdminApp
 */
angular.module('cutvApiAdminApp')

    .controller('MainCtrl', function ($scope, $http, $location, ChannelService) {

        // if (typeof cutv == 'undefined') {
        // } else {
        //     $scope.channels = cutv.channels;
        //     $scope.sources = sources;
        // }
        ChannelService.getChannels().then((data) => {
            $scope.channels = data;
            return $scope.channels;
        });


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

    });
