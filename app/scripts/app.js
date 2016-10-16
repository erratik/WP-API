'use strict';

/**
 * @ngdoc overview
 * @name cutvApiAdminApp
 * @description
 * # cutvApiAdminApp
 *
 * Main module of the application.
 */
angular
    .module('cutvApiAdminApp', [
        'ngRoute',
        'flow'
    ])
    .config(function ($routeProvider, flowFactoryProvider) {

        $routeProvider
            .when('/', {
                templateUrl: '/wp-content/plugins/cutv-api/views/channels.html',
                controller: 'MainCtrl',
                controllerAs: 'main'
            })
            // .when('/about', {
            //   templateUrl: 'views/about.html',
            //   controller: 'AboutCtrl',
            //   controllerAs: 'about'
            // })
            // .when('/myroute', {
            //   templateUrl: 'views/myroute.html',
            //   controller: 'MyrouteCtrl',
            //   controllerAs: 'myroute'
            // })
            .otherwise({
                redirectTo: '/'
            });
        //
        // flowFactoryProvider.defaults = {
        //     target: '/upload.php',
        //     permanentErrors: [404, 500, 501],
        //     maxChunkRetries: 1,
        //     chunkRetryInterval: 5000,
        //     simultaneousUploads: 4
        // };
        // flowFactoryProvider.on('catchAll', function (event) {
        //     // console.log('catchAll', arguments);
        // });

    });

