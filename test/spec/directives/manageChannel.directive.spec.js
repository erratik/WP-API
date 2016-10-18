'use strict';

describe('Directive: manageChannel', function () {

    beforeEach(module('cutvApiAdminApp', 'flow'));
    beforeEach(module('my.templates'));


    var element,
        childElem,
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
        // console.log(template);
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

       /* var angularElement = angular.element('<channel-image-uploader ' +
            'flow-init="{target: \'/wp-content/plugins/cutv-api/upload.php?channel='+$scope.channel.pid+'\', singleFile:true}" ' +
            'flow-files-submitted="$flow.upload()" ' +
            'flow-name="flow"> ' +
            '</channel-image-uploader>'
        );
        console.log(document.getElementsByClassName('row'));
        element.find('.row').prepend(angularElement);
        $compile(angularElement)($scope);
        $scope.$digest();*/


    }));
/*

    it('should check that the title of the channel is rendered', inject(function ($compile) {

        console.log(element);
        childElem = element.find('channel-image-uploader');
        childScope = element.scope();

        console.log(childScope);
        // childElem = $compile(childElem)(childScope);
        // console.log(childElem);
        //
        // childScope.$digest();
        //
        // console.log(childElem);
       /!* childTpl = tplCache.get('upload-channel-image.html');
        childElem = angular.element(element.find('channel-image-uploader'));
        childElem = $compile(childElem)($scope);
        $scope.$digest();
        // get the child directive's controller
        childScope = element.scope();*!/

        // console.log(childScope);

        // console.log(childElem);





    }));
*/


});
