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
        $scope.channels = ChannelService.getChannels();



    })
    .service('ChannelService', function () {
        this.getChannels = function() {
            return cutv.channels;
        };
        return this;
    });



