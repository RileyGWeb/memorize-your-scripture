import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';
import { VitePWA } from 'vite-plugin-pwa';

export default defineConfig({
    plugins: [
        laravel({
            input: ['resources/css/app.css', 'resources/js/app.js'],
            refresh: true,
        }),
        VitePWA({
            registerType: 'autoUpdate',
            injectRegister: 'auto',
            includeAssets: ['favicon.ico', 'robots.txt', 'apple-touch-icon.png'],
            manifest: {
                name: 'Memorize Your Scripture',
                short_name: 'Scripture Memory',
                start_url: '/',
                scope: '/',
                display: 'standalone',
                theme_color: '#111827',
                background_color: '#ffffff',
                icons: [
                    { src: '/icons/pwa-192x192.png', sizes: '192x192', type: 'image/png' },
                    { src: '/icons/pwa-512x512.png', sizes: '512x512', type: 'image/png' },
                    { src: '/icons/maskable-512.png', sizes: '512x512', type: 'image/png', purpose: 'maskable' },
                ],
            },
            workbox: {
                navigateFallback: '/offline',
                clientsClaim: true,
                skipWaiting: true,
                globPatterns: ['**/*.{js,css,html,ico,png,svg,webp,woff2}'],
                runtimeCaching: [
                    // Do NOT cache Livewire or auth/session endpoints
                    { urlPattern: /\/livewire\//i, handler: 'NetworkOnly', options: { cacheName: 'livewire' } },
                    { urlPattern: /\/(login|logout|register)(\/)?$/i, handler: 'NetworkOnly', options: { cacheName: 'auth' } },
                    { urlPattern: /\/password\//i, handler: 'NetworkOnly', options: { cacheName: 'auth' } },
                    { urlPattern: /\/sanctum\/csrf-cookie/i, handler: 'NetworkOnly', options: { cacheName: 'auth' } },
                    { urlPattern: /\/broadcasting\//i, handler: 'NetworkOnly', options: { cacheName: 'auth' } },

                    // HTML navigations
                    {
                        urlPattern: ({ request }) => request.mode === 'navigate',
                        handler: 'NetworkFirst',
                        options: {
                            cacheName: 'pages',
                            expiration: { maxEntries: 50, maxAgeSeconds: 60 * 60 * 24 * 7 },
                        },
                    },

                    // Static resources (JS/CSS/Workers)
                    {
                        urlPattern: ({ request }) => ['style', 'script', 'worker'].includes(request.destination),
                        handler: 'StaleWhileRevalidate',
                        options: { cacheName: 'static-resources' },
                    },

                    // Google Fonts
                    {
                        urlPattern: /^https:\/\/fonts\.(?:googleapis|gstatic)\.com\/.*$/i,
                        handler: 'CacheFirst',
                        options: {
                            cacheName: 'google-fonts',
                            expiration: { maxEntries: 20, maxAgeSeconds: 60 * 60 * 24 * 365 },
                        },
                    },

                    // Images (incl. /storage)
                    {
                        urlPattern: ({ request, url }) => request.destination === 'image' || url.pathname.startsWith('/storage/'),
                        handler: 'StaleWhileRevalidate',
                        options: {
                            cacheName: 'images',
                            expiration: { maxEntries: 200, maxAgeSeconds: 60 * 60 * 24 * 30 },
                        },
                    },
                ],
            },
            // Disable SW in dev by default (recommended). If you want it in dev, set enabled: true.
            devOptions: { enabled: false },
        }),
    ],
});
