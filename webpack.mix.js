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

mix.js('resources/js/cp.js', 'dist/js/cp.js')
    .copy('dist/js/cp.js', '../../../public/vendor/damcclean/commerce/js/cp.js')
    .js('resources/js/web.js', 'dist/js/web.js')
    .copy('dist/js/web.js', '../../../public/vendor/damcclean/commerce/js/web.js')
    .setPublicPath('dist');
