import laravel from 'laravel-vite-plugin'
import { defineConfig } from 'vite'
import statamic from '@statamic/cms/vite-plugin';

export default defineConfig({
    plugins: [
        statamic(),
        laravel({
            hotFile: 'dist/hot',
            publicDirectory: 'dist',
            input: [
                'resources/js/cp.js',
                // 'resources/css/cp.css',
            ],
        }),
    ],
});
