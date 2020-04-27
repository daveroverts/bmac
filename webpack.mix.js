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

require('dotenv').config();

mix.js('resources/js/app.js', 'public/js')
    .sass('resources/sass/app.scss', 'public/css', {
        prependData: '$envColorNavbar: ' + process.env.SITE_COLOR_NAVBAR + '; $envColorNavbarLinks: ' + process.env.SITE_COLOR_NAVBAR_LINKS + ';'
    })
    .copy('node_modules/tinymce/skins', 'public/js/skins');

mix.extract();

if (mix.inProduction()) {
    mix.version();
}
