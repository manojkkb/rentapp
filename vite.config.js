import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';
import tailwindcss from '@tailwindcss/vite';

export default defineConfig({
    plugins: [
        laravel({
            input: [
                'resources/css/app.css',
                'resources/js/app.js',
                'resources/js/order-wizard-datetime.js',
                'resources/js/order-booking-dates.js',
                'resources/js/coupon-date-pickers.js',
                'resources/js/vendor-support.js',
                'resources/js/storefront-shop.js',
                'resources/js/lib/fullcalendar.js',
            ],
            refresh: true,
        }),
        tailwindcss(),
    ],
    server: {
        host: '127.0.0.1',
        hmr: {
            host: '127.0.0.1',
        },
        watch: {
            ignored: ['**/storage/framework/views/**'],
        },
    },
});
