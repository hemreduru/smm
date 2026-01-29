import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';
// Tailwind disabled - Metronic has its own CSS framework
// import tailwindcss from '@tailwindcss/vite';

export default defineConfig({
    plugins: [
        laravel({
            input: [
                'resources/css/app.scss',
                'resources/js/app.js',
            ],
            refresh: true,
        }),
        // tailwindcss(), // Disabled to prevent CSS conflicts with Metronic
    ],
    resolve: { // Needed for Bootstrap/CoreUI
        alias: {
            '~coreui': '/node_modules/@coreui/coreui',
            '~bootstrap': '/node_modules/bootstrap',
        }
    },
    css: {
        preprocessorOptions: {
            scss: {
                api: 'modern-compiler', // or "modern"
                includePaths: ['node_modules'],
                quietDeps: true,
            },
        },
    },
    server: {
        watch: {
            ignored: ['**/storage/framework/views/**'],
        },
    },
});
