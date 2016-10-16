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
            template: '<div class="flex vertical column uploader-content"><div class="ui active inverted dimmer"><div class="ui loader"></div></div></div>',
            scope: true,
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
                    // event.preventDefault();//prevent file from uploading
                });

                // scope.$on('save_channel_image', function(mass){
                //     // scope.$flow.upload();
                //     // console.log(scope.$flow);
                //     // return $http.post('/wp-content/plugins/cutv-api/upload.php/?'+ObjectToUrl({'updateChannel': true, 'channel': 2, 'filename': scope.filename})).then(function(res) {
                //     //
                //     //     console.log(res);
                //     //
                //     // });
                // });
                //
                // function ObjectToUrl(myData) {
                //     var mapped = _.map(myData, function(o, k){
                //         return k + '=' + o;
                //     });
                //     var out = _.join(mapped, '&');
                //     return out;
                // }

            }
        };
    });
