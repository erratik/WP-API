'use strict';

describe('Controller: MainCtrl', function () {

  // load the controller's module
  beforeEach(module('cutvApiAdminApp'));

  var MainCtrl,
    scope, httpLocalBackend;
  // Initialize the controller and a mock scope
  beforeEach(inject(function ($injector, $controller, $rootScope) {
    scope = $rootScope.$new();

    MainCtrl = $controller('MainCtrl', {
      $scope: scope
      // place here mocked dependencies
    });

  }));

  beforeEach(inject(function ($httpBackend) {
    httpLocalBackend = $httpBackend;
  }));


  // it('should attach a list of channels to the scope', function () {
  //   expect(MainCtrl.channels.length).toBeDefined();
  // });

  it('should get stuff', function () {

        httpLocalBackend.whenGET('*').respond(readJSON('test/mock/channels.json'));

        expect(MainCtrl.channels.length).toBe(2);

      }
  );



  // it('should attach a list of channels to the scope', function () {
  //     // console.log(MainCtrl);
  //     var valid_respond = readJSON('test/mock/channels.json');
  //     $httpBackend.whenGET(/.*/).respond(valid_respond);
  //       expect(MainCtrl).toBeDefined();
  // });


});
