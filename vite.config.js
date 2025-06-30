import { defineConfig } from 'vite'
import laravel from 'laravel-vite-plugin'
import vue from '@vitejs/plugin-vue';
import { viteExternalsPlugin } from 'vite-plugin-externals';
import statamic from './vendor/statamic/cms/resources/js/vite-plugin';

export default defineConfig(({ command, mode }) => {
    return {
        plugins: [
            statamic(),
            laravel({
                hotFile: 'dist/hot',
                publicDirectory: 'dist',
                input: ['resources/js/cp.js', 'resources/css/cp.css'],
            }),
            vue(),
            viteExternalsPlugin({ vue: 'Vue', pinia: 'Pinia', 'vue-demi': 'Vue' }),
        ],
        server: {
            hmr: false
        }
    }
});
