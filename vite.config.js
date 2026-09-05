import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';

export default defineConfig({
    plugins: [
        laravel({
            input: [
                'resources/css/app.css',
                'resources/js/app.js',
                'resources/css/website.css',
                'resources/css/website-base.css',
                'resources/css/website-home.css',
                'resources/css/website-after.css',
                'resources/css/website-theme.css',
                'resources/css/website-rtl.css',
                'resources/css/website-header.css',
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
