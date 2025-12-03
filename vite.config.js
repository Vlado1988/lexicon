import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';

export default defineConfig({
    plugins: [
        laravel({
            input: [
                'resources/css/app.css', 
                'resources/js/app.js',
                'resources/css/admin/app.css',
                'resources/js/admin/app.js',
                'resources/js/admin/bootstrap.js',
                'resources/js/admin/functions.js',
            ],
            refresh: true,
        }),
    ],
});
