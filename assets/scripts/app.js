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
    .config(function ($routeProvider) {

        $routeProvider
            .when('/', {
                templateUrl: '/wp-content/plugins/cutv-api/views/main.html',
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
    });

