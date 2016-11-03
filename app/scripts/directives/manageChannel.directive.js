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
                channel: '=',
                sources: '='
            },
            template: '<div><div class="ui active inverted dimmer"><div class="ui text loader">LOADING CHANNELS</div></div></div>',
            controller: function($scope, $element, $compile) {




            },
            controllerAs: 'fatherCtrl',
            link: function (scope, element, attrs) {



                _.forEach(scope.channel, function(value, key){
                    var convertedValue = value == 'false' ? false : value;
                        convertedValue = value == 'true' ? 1 : value;
                        convertedValue = !_.isNaN(Number(value)) ? Number(value) : value;

                    if ( key == 'enabled') {
                        convertedValue = value != 'false' && value != '' ? false : true;
                    }

                    scope.channel[key] = convertedValue;

                });

                // acking it becase of semantic sucking
                scope.channel.enabled  = !scope.channel.enabled;



                scope.query = '';

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

                    element.prepend(angularElement);
                    $compile(angularElement)(scope);


                });


                scope.hasVideos = function (source) {
                    return Boolean(source.videos['published_count']);
                };

                // todo: update .spec for deleteChannel()
                scope.updateChannel = function() {
                    // acking it becase of semantic sucking
                    scope.channel.enabled  = !scope.channel.enabled;
                    var data = {
                        action: 'cutv_update_source_categories',
                        sources: JSON.stringify(_.map(_.filter(scope.channel.sources, {'selected': true}), function(o){ return o.ID })), //,
                        channel: scope.channel.pid,
                        enabled: !scope.channel.enabled,
                        removing_sources: JSON.stringify(_.map(_.filter(scope.channel.sources, {'selected': false}), function(o){ return o.ID })),
                        source_count: scope.channel.source_count
                    };

                    return $http.get(ajaxurl , {params: data}).then(function(res) {

                        res = JSON.parse(res.data.split('data:')[1]);

                        scope.channel.source_count = res.length;

                        makeSourceObj();

                    });

                };


                // todo: update .spec for deleteChannel()
                scope.deleteChannel = function() {
                    var createChannelRequest = {
                        'action' : 'cutv_remove_channel',
                        id: scope.channel.pid
                    };


                    return $http.post(ajaxurl, createChannelRequest).then(function(res) {

                        console.log(res);
                        element.parent().remove();
                        scope.$destroy();

                    });

                };


                function makeSourceObj() {

                    var sources = scope.$parent.sources;

                        var channel_id = scope.channel.pid;

                        scope.channel['sources'] = [];
                        scope.channel['counts'] = {
                            unpublished : 0,
                            published   : 0
                        };

                        _.forEach(sources['all'], function(source){
                            source = _.clone(source);
                            source.selected = false;
                            source.onChannels = [];
                            scope.channel['sources'].push(source);
                        });

                        _.forEach(sources['assigned'], function(source, i) {
                            _.findIndex(source.categories, function(o) {
                                if (o == channel_id) {

                                    scope.channel['sources'][i].selected = true;
                                    scope.channel['sources'][i].onChannels.push(channel_id);

                                    scope.channel['counts']['unpublished'] =  scope.channel['counts']['unpublished']+Number(source['videos']['unpublished_count']);

                                    scope.channel['counts']['published'] =  scope.channel['counts']['published']+Number(source['videos']['published_count']) ;


                                }
                            });
                        });

                        scope.channel.countAssignedSources = _.filter(scope.channel['sources'], 'selected').length;

                        scope.channel.countAllSources = scope.channel['sources'].length;

                }

                (function initialize() {
                    makeSourceObj();
                })();

            }
        };
    });
