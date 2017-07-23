'use strict';

/**
 * @ngdoc function
 * @name cutvApiAdminApp.controller:ChannelSourceCtrl
 * @description
 * # MainCtrl
 * Controller of the cutvApiAdminApp
 */
angular.module('cutvApiAdminApp')

    .controller('ChannelSourceCtrl', function ($scope, $http, $location, $routeParams, ChannelService) {

        // init
        $scope.channelName = $routeParams.channelName;
        $scope.channelId = $routeParams.channelId;

    });
