'use strict';

describe('Controller: MainCtrl', function () {

    // load the controller's module
    beforeEach(module('cutvApiAdminApp'));

    var MainCtrl,
        $scope, httpLocalBackend, $resource;

    // Initialize the controller and a mock scope
    beforeEach(inject(function ($injector, $controller, $rootScope) {
        $scope = $rootScope.$new();

        MainCtrl = $controller('MainCtrl', {
            $scope: $scope
            // place here mocked dependencies
        });

    }));
    //
    // beforeEach(inject(function ($httpBackend) {
    //     httpLocalBackend = $httpBackend;
    //     // $resource = $resource;
    // }));


    it('should have channels', function () {

        $scope.channels = readJSON('test/mock/channels.json');

        expect($scope.channels).toBeDefined();

    });


    // it('should attach a list of channels to the scope', function () {
    //     // console.log(MainCtrl);
    //     var valid_respond = readJSON('test/mock/channels.json');
    //     $httpBackend.whenGET(/.*/).respond(valid_respond);
    //       expect(MainCtrl).toBeDefined();
    // });


});
