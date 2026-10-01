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
                // Page-specific styles
                'resources/css/pages/attractions-by-city.css',
                'resources/css/pages/attractions-index.css',
                'resources/css/pages/attractions-show.css',
                'resources/css/pages/blogs-index.css',
                'resources/css/pages/blogs-show.css',
                'resources/css/pages/checkout-show.css',
                'resources/css/pages/checkout-status.css',
                'resources/css/pages/contact-us.css',
                'resources/css/pages/day-tours-index.css',
                'resources/css/pages/destinations-index.css',
                'resources/css/pages/destinations-show.css',
                'resources/css/pages/enquiry-form.css',
                'resources/css/pages/multi-country.css',
                'resources/css/pages/nile-cruise-details.css',
                'resources/css/pages/nile-cruise-pricing.css',
                'resources/css/pages/nile-cruises-index.css',
                'resources/css/pages/nile-cruises-listing.css',
                'resources/css/pages/nile-cruises-luxor-aswan.css',
                'resources/css/pages/offers.css',
                'resources/css/pages/packages-index.css',
                'resources/css/pages/packages-show.css',
                'resources/css/pages/search.css',
                'resources/css/pages/static-pages-index.css',
                'resources/css/pages/tailor-made.css',
                'resources/css/pages/travel-packages-index.css',
                'resources/css/pages/travel-tips.css',
                // Page-specific scripts
                'resources/js/pages/blogs-index.js',
                'resources/js/pages/blogs-show.js',
                'resources/js/pages/checkout-show.js',
                'resources/js/pages/contact-us.js',
                'resources/js/pages/destinations-index.js',
                'resources/js/pages/enquiry-form.js',
                'resources/js/pages/nile-cruise-details.js',
                'resources/js/pages/packages-show.js',
                'resources/js/pages/search.js',
                'resources/js/pages/tailor-made.js',
                'resources/js/pages/travel-tips.js',
            ],
            refresh: true,
        }),
    ],
    esbuild: {
        legalComments: 'none',
        minifyWhitespace: true,
        minifyIdentifiers: true,
        minifySyntax: true,
    },
    build: {
        cssCodeSplit: true,
        minify: 'esbuild',
        cssMinify: true,
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
