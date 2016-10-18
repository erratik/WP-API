/*

'use strict';

describe('Directive: channelUploader', function () {

    beforeEach(module('cutvApiAdminApp', 'flow'));
    beforeEach(module('my.templates'));


    var element,
        $scope,
        tplCache,
        template;
    var channels = readJSON('test/mock/channels.json');


    // // Initialize the controller and a mock scope
    beforeEach(inject(function ($rootScope, $compile, $templateCache) {

        $scope = $rootScope.$new();
        tplCache = $templateCache;
        template = tplCache.get('upload-channel-image.html');

        $scope.channel = channels[0];

        var channelStr = JSON.stringify($scope.channel);
        expect(channelStr).toContain('pid');
        expect(channelStr).toContain('playlist_name');
        expect(channelStr).toContain('playlist_order');

        element = angular.element(template);
        element = $compile(element)($scope);
        $scope.$digest();

    }));


    it('should make the element visible', inject(function ($compile, $templateRequest) {

        // var channelName = $scope.channel.playlist_name;
        // expect(element.find('a').text()).toEqual(channelName);

    }));

});
*/
