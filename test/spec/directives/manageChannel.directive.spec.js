'use strict';

describe('Directive: manageChannel', function () {

    beforeEach(module('cutvApiAdminApp', 'flow'));
    beforeEach(module('my.templates'));


    var element,
        childElem,
        childElemTpl,
        childScope,
        childTpl,
        $scope,
        tplCache,
        template;
    var channels = readJSON('test/mock/channels.json');


    // // Initialize the controller and a mock scope
    beforeEach(inject(function ($rootScope, $compile, $templateCache) {

        $scope = $rootScope.$new();
        tplCache = $templateCache;
        template = tplCache.get('manage-channel.html');
        $scope.channel = channels[0];

        var channelStr = JSON.stringify($scope.channel);
        expect(channelStr).toContain('pid');
        expect(channelStr).toContain('playlist_name');
        expect(channelStr).toContain('playlist_order');

        element = angular.element(template);
        element = $compile(element)($scope);

        $scope.$digest();

    }));


    it('should check that the title of the channel is rendered', inject(function ($compile) {

        var channelName = $scope.channel.playlist_name;
        expect(element.find('a').text()).toEqual(channelName);


    }));

    var mockedFatherCtrl

    it('should add the <channel-image-uploader> directive', inject(function ($compile) {


        var compiledDirective = '<div class="row">';
        var angularElement = angular.element(compiledDirective + '<channel-image-uploader ' +
            'flow-init="{target: \'/wp-content/plugins/cutv-api/upload.php?channel='+$scope.channel.pid+'\', singleFile:true}" ' +
            'flow-files-submitted="$flow.upload()" ' +
            'flow-name="flow"> ' +
            '</channel-image-uploader>' +
           template.split(/<div class="row">\s+/gmi)[1]
        );

        element = $compile(angularElement)($scope);
        $scope.$digest();

        childTpl = tplCache.get('upload-channel-image.html');

        expect(childTpl).toContain('flow-btn flow-attrs="{accept:\'image/\*\'}"');

        childElem = angular.element(element.find('channel-image-uploader'));
        childElem.html(childTpl);
        childScope = element.scope();
        childElem = $compile(childElem)(childScope);
        $scope.$digest();

        var cutv_channel_img =  (typeof childScope.channel['cutv_channel_img'] == 'undefined') ? false : childScope.channel['cutv_channel_img'];
        if (cutv_channel_img) {
            expect(childElem.find('img')[1].getAttribute('src')).toContain(cutv_channel_img);
        } else {
            // console.log(childElem.find('img')[1].getAttribute('src'));
            expect(childElem.find('img')[1].getAttribute('src')).toContain('placeholdit');
        }


    }));


});
