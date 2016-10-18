// Karma configuration
// Generated on 2016-10-10

module.exports = function (config) {
    'use strict';

    config.set({
        // enable / disable watching file and executing tests whenever any file changes
        autoWatch: true,

        // base path, that will be used to resolve files and exclude
        basePath: '../',

        // testing framework to use (jasmine/mocha/qunit/...)
        // as well as any additional frameworks (requirejs/chai/sinon/...)
        frameworks: [
            'jasmine'
            // 'jasmine-jquery',

        ],

        // list of files / patterns to load in the browser
        files: [
            // bower:js
            '/bower_components/angular/angular.js',
            '/bower_components/angular-mocks/angular-mocks.js',
            '/bower_components/angularjs-jasmine-matchers/dist/matchers.js',
            '/bower_components/angular-route/angular-route.js',
            '/bower_components/angular-resource/angular-resource.js',
            '/bower_components/handlebars/handlebars.js',
            '/bower_components/flow.js/dist/flow.js',
            '/bower_components/ng-flow/dist/ng-flow.js',
            // endbower
            '/bower_components/ng-flow/dist/ng-flow-standalone.js',
        ],

        // list of files / patterns to exclude
        exclude: [
            'app/scripts/directives/*.js',
            '/bower_components/handlebars/handlebars.js'
        ],

        // web server port
        port: 7537,

        // Start these browsers, currently available:
        // - Chrome
        // - ChromeCanary
        // - Firefox
        // - Opera
        // - Safari (only Mac)
        // - PhantomJS
        // - IE (only Windows)
        browsers: [
            'PhantomJS'
        ],

        // generate js files from html templates
        preprocessors: {
            'app/templates/*.html': 'ng-html2js'
        },

        // Which plugins to enable
        plugins: [
            'karma-chrome-launcher',
            'karma-phantomjs-launcher',
            'karma-jasmine',
            'karma-jasmine-jquery',
            'karma-read-json',
            'karma-ng-html2js-preprocessor',
            'angularjs-jasmine-matchers'

        ],


        ngHtml2JsPreprocessor: {
            stripPrefix: 'app/templates/',
            moduleName: 'my.templates'
        },

        // Continuous Integration mode
        // if true, it capture browsers, run tests and exit
        singleRun: false,

        colors: true,

        // level of logging
        // possible values: LOG_DISABLE || LOG_ERROR || LOG_WARN || LOG_INFO || LOG_DEBUG
        logLevel: config.LOG_INFO,

        // Uncomment the following lines if you are using grunt's server to run the tests
        // proxies: {
        //   '/': 'http://localhost:9000/'
        // },
        // URL root prevent conflicts with the site root
        // urlRoot: '_karma_'
    });
};
