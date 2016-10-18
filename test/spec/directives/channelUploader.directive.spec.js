describe('test childDirective', function(){
    var $rootScope, $compile,
        scope, element, ctrl;

    console.log('test2');
    var mockedFatherCtrl = {

    };


    beforeEach(inject(function($rootScope, $compile){
            console.log('test3');
            scope = $rootScope.$new();
            // element = $compile(angular.element('<div><channel-image-uploader></channel-image-uploader></div>'))(scope);
            // console.log(element)
            // // add the mock controller to the father
            // element.data('$manageChannelController', mockedFatherCtrl);
            //
            // // get the child directive's element
            // element = element.find('channel-image-uploader');
            //
            // // get the child directive's controller
            // ctrl = element.scope().childCtrl;
        })
    );
    
    it('', function () {
        
    });
})