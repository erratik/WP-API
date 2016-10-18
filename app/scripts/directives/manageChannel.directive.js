'use strict';

/**
 * @ngdoc directive
 * @name cutvApiAdminApp.directive:manageChannel
 * @description
 * # manageChannel
 */
angular.module('cutvApiAdminApp')
    .directive('manageChannel', function ($templateRequest, $http, $compile) {
        return {
            restrict: 'E',
            replace: true,
            scope: {
                channel: '='
            },
            template: '<div><div class="ui active inverted dimmer"><div class="ui text loader">LOADING CHANNELS</div></div></div>',
            controller: function($scope, $element, $compile) {




            },
            link: function (scope, element, attrs) {


                scope.query = '';
                scope.updateChannel = function() {

                    var data = {
                        action: 'cutv_update_source_categories',
                        sources: JSON.stringify(_.map(_.filter(scope.channel.sources, {'selected': true}), function(o){ return o.ID })), //,
                        channel: scope.channel.pid,
                        removing_sources: JSON.stringify(_.map(_.filter(scope.channel.sources, {'selected': false}), function(o){ return o.ID })),
                        source_count: scope.channel.source_count
                    };

                    return $http.get(ajaxurl , {params: data}).then(function(res) {
                        res = JSON.parse(res.data.split('data:')[1]);
                        // console.log(res);
                        scope.channel.source_count = res.length;

                        // scope.$broadcast('save_channel_image', {name: data.channel});

                    });

                };


                $templateRequest('/wp-content/plugins/cutv-api/app/templates/manage-channel.html').then(function(html){

                    var template = angular.element(html);
                    element.html(template);
                    $compile(template)(scope);

                }).then(function () {

                    var angularElement = angular.element('<channel-image-uploader ' +
                        'flow-init="{target: \'/wp-content/plugins/cutv-api/upload.php?channel='+scope.channel.pid+'\', singleFile:true}" ' +
                        'flow-files-submitted="$flow.upload()" ' +
                        'flow-name="flow"> ' +
                        '</channel-image-uploader>'
                    );

                    element.find('.row').prepend(angularElement);
                    $compile(angularElement)(scope);

                });

            }
        };
    });
