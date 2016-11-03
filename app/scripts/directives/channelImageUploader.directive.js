'use strict';

/**
 * @ngdoc directive
 * @name cutvApiAdminApp.directive:channelUploader
 * @description
 * # channelUploader
 */
angular.module('cutvApiAdminApp')
    .directive('channelImageUploader', function ($templateRequest, $compile, $http) {
        return {
            restrict: 'E',
            replace: true,
            template: '<div class="flex vertical three wide column uploader-content"><div class="ui active inverted dimmer"><div class="ui loader"></div></div></div>',
            scope: true,
            controllerAs: 'childCtrl',
            require: '^^manageChannel',
            link: function(scope, element) {

                if (!_.isNil(scope.$flow)) {
                    // console.log(scope.$flow);
                    $templateRequest('/wp-content/plugins/cutv-api/app/templates/upload-channel-image.html').then(function(html){
                        var template = angular.element(html);
                        element.html(template);
                        $compile(template)(scope);
                    });

                }

                scope.$on('flow::fileAdded', function (event, $flow, flowFile) {
                    console.log(flowFile);
                    scope.filename = flowFile.name;
                });


            }
        };
    });
