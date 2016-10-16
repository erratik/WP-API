'use strict';

describe('Directive: manageChannel', function () {

  // load the directive's module
  beforeEach(module('cutvApiAdminApp'));

  var element,
    scope;

  beforeEach(inject(function ($rootScope) {
    scope = $rootScope.$new();
  }));

  it('should make hidden element visible', inject(function ($compile) {
    element = angular.element('<manage-channel></manage-channel>');
    element = $compile(element)(scope);
    expect(element.text()).toBe('this is the manageChannel directive');
  }));
});
