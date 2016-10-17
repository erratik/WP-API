'use strict';

/**
 * @ngdoc function
 * @name cutvApiAdminApp.controller:MainCtrl
 * @description
 * # MainCtrl
 * Controller of the cutvApiAdminApp
 */
angular.module('cutvApiAdminApp')

    .controller('MainCtrl', function ($scope, ChannelService) {

        if (typeof cutv == 'undefined') {
            ChannelService.getChannels().then(function (data) {
                $scope.channels = data
            });
        } else {
            $scope.channels = cutv.channels;
        }

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

