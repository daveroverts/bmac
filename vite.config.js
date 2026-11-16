import { defineConfig, loadEnv } from 'vite';
import laravel from 'laravel-vite-plugin';
import { viteStaticCopy } from 'vite-plugin-static-copy';
import purgecss from '@fullhuman/postcss-purgecss';

export default defineConfig(({ command, mode }) => {
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
                    { src: 'node_modules/tinymce/icons', dest: 'js', rename: { stripBase: 2 } },
                    { src: 'node_modules/tinymce/models', dest: 'js', rename: { stripBase: 2 } },
                    { src: 'node_modules/tinymce/themes', dest: 'js', rename: { stripBase: 2 } },
                    { src: 'node_modules/tinymce/skins', dest: 'js', rename: { stripBase: 2 } },
                ],
            }),
        ],
        css: {
            // PurgeCSS runs as a PostCSS plugin, so it is part of the CSS that Vite
            // hashes: the filename changes whenever the set of kept classes changes.
            // Only for builds, the dev server serves the full CSS.
            postcss: {
                plugins: command !== 'build' ? [] : [
                    purgecss({
                        content: [
                            './resources/views/**/*.blade.php',
                            './resources/js/**/*.js',
                            './vendor/laravel/framework/src/Illuminate/Pagination/resources/views/*bootstrap-5.blade.php',
                            './vendor/livewire/livewire/src/Features/SupportPagination/views/*bootstrap.blade.php',
                        ],
                        safelist: {
                            // A "standard" entry only keeps a rule when every other class in its
                            // selector is used as well, so unused variants of a library still get
                            // purged. "deep" keeps every rule that mentions a matching class.
                            standard: [
                                // Added at runtime by Bootstrap's JS (collapse, dropdown, modal).
                                'show',
                                'collapsing',
                                'modal-open',
                                'modal-backdrop',
                                'modal-static',

                                // SweetAlert2, without the toast and right-to-left styles.
                                // Remove the lookahead when using `toast: true`.
                                /^swal2-(?!toast|rtl)/,
                                'output',

                                // Tom Select, plus the state classes it toggles at runtime. The
                                // multi-select, right-to-left and plugin styles are not listed,
                                // add `multi`, `rtl` or `/^plugin-/` when those get used.
                                /^ts-/,
                                'single',
                                'focus',
                                'input-active',
                                'input-hidden',
                                'dropdown-active',
                                'has-items',
                                'has-options',
                                'full',
                                'not-full',
                                'loading',
                                'preloaded',
                                'locked',
                                'readonly',
                                'required',
                                'invalid',
                                'disabled',
                                'active',
                                'selected',
                                'item',
                                'option',
                                'optgroup',
                                'optgroup-header',
                                'create',
                                'no-results',
                                'spinner',
                                'highlight',
                            ],
                            deep: [/^flatpickr-/],
                        },
                        // Attributes that only exist at runtime: Tom Select's option state, and
                        // the `[class^=swal2-...]` selectors SweetAlert2 uses for its icons.
                        dynamicAttributes: ['class', 'data-selectable', 'data-disabled'],
                    }),
                ],
            },
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
