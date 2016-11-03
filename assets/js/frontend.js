var ajaxurl = '/wp-admin/admin-ajax.php';

jQuery( document ).ready( function ( e ) {

    $('.video-carousel-title, .video-module-title, .video-block-container-wrapper .more_title').each(function(){
        $(this).children().wrapAll('<div></div>');
    });
    $('.main-inner .first-entry:not(:first-child)').remove();

    _cutv.ajax(ajaxurl, {
            action : 'cutv_get_channels',
            json: true
    }).then(function (data) {
        console.log(data);

        $('.page-wrapper').prepend($('.cutv-channels'));
        _cutv.render({
            target: $('.cutv-channels'), // $el
            template: $('#cutv-channels'), // $el, handlebar template
            data: data, // data object
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
