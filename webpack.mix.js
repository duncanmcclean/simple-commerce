let mix = require('laravel-mix');

/*
 |--------------------------------------------------------------------------
 | Mix Asset Management
 |--------------------------------------------------------------------------
 |
 | Mix provides a clean, fluent API for defining some Webpack build steps
 | for your Laravel application. By default, we are compiling the Sass
 | file for the application as well as bundling up all the JS files.
 |
 */

// WIP: work on a better solution that just copying over...

mix.js('resources/js/cp.js', 'dist/js/cp.js')
    .copy('dist/js/cp.js', '../../../public/vendor/doublethreedigital/simple-commerce/js/cp.js')
    .setPublicPath('dist');
