/*
'use strict';

describe('Directive: channelUploader', function () {

  // load the directive's module
  beforeEach(module('cutvApiAdminApp'));

  var element,
    scope;

  beforeEach(inject(function ($rootScope) {
    scope = $rootScope.$new();
  }));

  it('should make hidden element visible', inject(function ($compile) {
    element = angular.element('<channel-uploader></channel-uploader>');
    element = $compile(element)(scope);
    expect(element.text()).toBe('this is the channelUploader directive');
  }));
});
*/
