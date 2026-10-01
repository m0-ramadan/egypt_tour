import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';
import { PurgeCSS } from 'purgecss';

const purgeHomeBundles = () => ({
    name: 'purge-home-bundles',
    apply: 'build',
    async generateBundle(_options, bundle) {
        const homeCssAssets = Object.values(bundle).filter(
            (asset) => asset.type === 'asset' && [
                'website-home-critical.css',
                'website-home-deferred.css',
            ].includes(asset.name),
        );

        await Promise.all(homeCssAssets.map(async (homeCss) => {
            const source = typeof homeCss.source === 'string'
                ? homeCss.source
                : Buffer.from(homeCss.source).toString('utf8');
            const [result] = await new PurgeCSS().purge({
                content: [
                    'resources/views/website/layouts/master.blade.php',
                    'resources/views/website/layouts/header.blade.php',
                    'resources/views/website/layouts/footer.blade.php',
                    'resources/views/website/pages/home.blade.php',
                    'resources/js/website.js',
                ],
                css: [{ raw: source }],
                safelist: {
                    standard: [
                        'active', 'disabled', 'fade', 'show', 'open', 'scrolled',
                        'rotated', 'is-visible', 'is-active', 'modal-open',
                    ],
                    deep: [/^mobile-/, /^dropdown-/, /^modal-/, /^iti__/],
                },
                keyframes: true,
                fontFace: false,
            });

            homeCss.source = result.css;
        }));
    },
});

export default defineConfig({
    plugins: [
        purgeHomeBundles(),
        laravel({
            input: [
                'resources/css/tokens.css',
                'resources/css/components.css',
                'resources/css/app.css',
                'resources/js/app.js',
                'resources/css/website.css',
                'resources/css/website-base.css',
                'resources/css/website-home.css',
                'resources/css/website-home-critical.css',
                'resources/css/website-home-deferred.css',
                'resources/css/website-after.css',
                'resources/css/website-theme.css',
                'resources/css/website-rtl.css',
                'resources/css/website-header.css',
                'resources/css/egypt-tour-pro-final.css',
                'resources/js/website.js',
            ],
            refresh: true,
        }),
    ],
    build: {
        cssCodeSplit: true,
        minify: 'esbuild',
        sourcemap: false,
        target: 'es2020',
        assetsInlineLimit: 0,
        rollupOptions: {
            output: {
                entryFileNames: 'assets/[name]-[hash].js',
                chunkFileNames: 'assets/[name]-[hash].js',
                assetFileNames: 'assets/[name]-[hash][extname]',
            },
        },
    },
});
