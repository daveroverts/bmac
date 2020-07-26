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
        additionalData: '$envColorPrimary: ' + (process.env.BOOTSTRAP_COLOR_PRIMARY || '#2C3E50') + '; $envColorSecondary: ' + (process.env.BOOTSTRAP_COLOR_SECONDARY || '#95a5a6') + '; $envColorTertiary: ' + (process.env.BOOTSTRAP_COLOR_TERTIARY || '#18BC9C') + '; $envColorSuccess: ' + (process.env.BOOTSTRAP_COLOR_SUCCESS || '#18BC9C') + '; $envColorWarning: ' + (process.env.BOOTSTRAP_COLOR_WARNING || '#F39C12') + '; $envColorDanger: ' + (process.env.BOOTSTRAP_COLOR_DANGER || '#E74C3C') + ';'
    })
    .copy('node_modules/tinymce/skins', 'public/js/skins');

mix.extract();

if (mix.inProduction()) {
    mix.version();
}
