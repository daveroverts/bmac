import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';

export default defineConfig({
    plugins: [
        laravel({
            input: [
                'resources/sass/app.scss',
                'resources/js/alpine.js',
                'resources/js/app.js',
                'resources/js/tinymce.js',
            ],
            refresh: true,
        }),
    ],
});
