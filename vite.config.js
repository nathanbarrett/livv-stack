import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';
import vue from "@vitejs/plugin-vue";
import vuetify from 'vite-plugin-vuetify';
import path from 'path';

export default defineConfig({
    server: {
        host: '0.0.0.0',
        cors: true,
        hmr: process.env.CODESPACES === 'true' ? {
            clientPort: 443
        } : {
            host: 'localhost',
        },
    },
    resolve: {
        // keep in sync with path aliases in tsconfig.json
        alias: {
            '@js': path.resolve(__dirname, 'resources/js'),
        },
    },
    plugins: [
        laravel({
            input: ['resources/css/app.css', 'resources/js/app.ts'],
            refresh: true,
        }),
        vue({
            template: {
                transformAssetUrls: {
                    base: null,
                    includeAbsolute: false,
                },
            },
        }),
        vuetify(),
    ],
});
