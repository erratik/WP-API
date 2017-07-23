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
            // require: '^^channelItem',
            link: function(scope, element) {

                scope.error = function ( $file, $message, $flow ) {
                    console.log('test error with image');
                    console.log( $file,  $flow );
                }

                scope.complete = function ( $file, $message, $flow ) {
                    console.log('test 2 error with image');
                    debugger;
                }


                if (!!scope.$flow) {
                    console.log(scope.$flow);

                    $templateRequest('/wp-content/plugins/cutv-api/app/templates/upload-channel-image.html').then(function(html){
                        var template = angular.element(html);
                        element.html(template);
                        $compile(template)(scope);
                    });

                }

                scope.$on('flow::fileAdded', function (event, $flow, flowFile) {
                    console.log(flowFile);
                    if (!flowFile.error) {
                        scope.filename = 'http://via.placeholder.com/100/C00D0D/000?text=oops.';

                        // event.preventDefault();//prevent file from uploading
                        // debugger;
                    } else {

                        scope.filename = flowFile.name;
                    }
                });


            }
        };
    });
