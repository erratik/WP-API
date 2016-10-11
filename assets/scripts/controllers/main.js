'use strict';

/**
 * @ngdoc function
 * @name cutvApiAdminApp.controller:MainCtrl
 * @description
 * # MainCtrl
 * Controller of the cutvApiAdminApp
 */
angular.module('cutvApiAdminApp')

    .controller('MainCtrl', function ($scope, $http) {
        $scope.channels = cutv.channels;

        $scope.unassignedSources = sources.unassigned;

        console.log($scope);

        $scope.updateChannel = function(channel) {

            var data = {
                action: 'cutv_update_source_categories',
                sources: JSON.stringify(_.map(_.filter(channel.sources, {'selected': true}), function(o){ return o.ID })), //,
                channel: channel.pid,
                removing_sources: JSON.stringify(_.map(_.filter(channel.sources, {'selected': false}), function(o){ return o.ID })),
                source_count: channel.source_count
            };

            return $http.get(ajaxurl , {params: data}).then(function(res) {
                res = JSON.parse(res.data.split('data:')[1]);
                console.log(res);
                var channel = _.find($scope.channels, {pid: '2'});
                channel.source_count = res.length;



            });
        }
    });



