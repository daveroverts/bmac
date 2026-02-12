import { defineConfig, loadEnv } from 'vite';
import laravel from 'laravel-vite-plugin';
import { viteStaticCopy } from 'vite-plugin-static-copy';
import purgecss from 'vite-plugin-purgecss';

export default defineConfig(({ mode }) => {
    const env = loadEnv(mode, process.cwd(), '');

    return {
        plugins: [
            laravel({
                input: [
                    'resources/sass/app.scss',
                    'resources/js/app.js',
                    'resources/js/flatpickr.js',
                    'resources/js/tinymce.js',
                ],
                refresh: true,
            }),
            viteStaticCopy({
                targets: [
                    { src: 'node_modules/tinymce/icons/*', dest: 'js/icons' },
                    { src: 'node_modules/tinymce/models/*', dest: 'js/models' },
                    { src: 'node_modules/tinymce/themes/*', dest: 'js/themes' },
                    { src: 'node_modules/tinymce/skins/*', dest: 'js/skins' },
                ],
            }),
            purgecss({
                content: [
                    './resources/views/**/*.blade.php',
                    './resources/js/**/*.js',
                    './vendor/laravel/framework/src/Illuminate/Pagination/resources/views/*bootstrap-5.blade.php',
                ],
                safelist: {
                    deep: [/\b(ui|flatpickr)-\S*$/, /^swal2-/],
                },
            }),
        ],
        css: {
            preprocessorOptions: {
                scss: {
                    additionalData:
                        '$envColorPrimary: ' + (env.BOOTSTRAP_COLOR_PRIMARY || '#2C3E50') + '; ' +
                        '$envColorSecondary: ' + (env.BOOTSTRAP_COLOR_SECONDARY || '#95a5a6') + '; ' +
                        '$envColorTertiary: ' + (env.BOOTSTRAP_COLOR_TERTIARY || '#18BC9C') + '; ' +
                        '$envColorSuccess: ' + (env.BOOTSTRAP_COLOR_SUCCESS || '#18BC9C') + '; ' +
                        '$envColorWarning: ' + (env.BOOTSTRAP_COLOR_WARNING || '#F39C12') + '; ' +
                        '$envColorDanger: ' + (env.BOOTSTRAP_COLOR_DANGER || '#E74C3C') + ';',
                },
            },
        },
    };
});
