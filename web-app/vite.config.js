import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';

export default defineConfig({
    plugins: [
        laravel({
            input: [
                'resources/css/app.css', 
                'resources/css/grunge-chaos-scoped.css',
                'resources/css/chaos-effects.css',
                'resources/js/app.js', 
                'resources/js/simple-mining.js'
            ],
            refresh: false,
        }),
    ],
    build: {
        manifest: true,
        outDir: 'public/build',
        rollupOptions: {
            input: [
                'resources/css/app.css', 
                'resources/css/grunge-chaos-scoped.css',
                'resources/css/chaos-effects.css',
                'resources/js/app.js', 
                'resources/js/simple-mining.js'
            ],
        },
    },
});
