var ajaxurl = '/wp-admin/admin-ajax.php';

jQuery( document ).ready( function ( e ) {
    console.log('test');

    _cutv.ajax(ajaxurl, {
            action : 'cutv_get_channels',
            json: true
    }).then(function (data) {
        console.log(data);
        _cutv.render({
            target: $('.primary-menu'), // $el
            template: $(''), // $el, handlebar template
            data: null, // data object
            callback: null
        });
    });
});

_cutv.render = function (options) {
    'use strict';
    var params = {
        target: null, // $el
        template: null, // $el, handlebar template
        data: null, // data object
        callback: null
    };

    $.extend(params, options);
    var $template = params.template;
    var $target   = params.target;

    var source   = $template.html();
    var template = Handlebars.compile(source);
    var wrapper  = params.data;
    var html    = template(wrapper);

    $target.html(html);

    if (typeof params.callback === 'function') {
        params.callback();
    }
}
