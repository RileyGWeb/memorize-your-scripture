## stuff to do
### Use TDD! When doing a new feature, write tests for how it should function first. Only once tests are passing, then implement the feature.
### Always create test coverage for new features!
### Always run all tests after every change is completed!
### Check off items as you complete them.
### Do not stop iterating until all tests are complete!


[ ] The user's profile page needs work. It looks like it's in dark mode, and is not mobile responsive. Remove the Browser Sessions section. Add one to change the background image.

[ ] Create an about page. Just put a placeholder for a picture of myself and some words.

[ ] We need to build out the quiz functoinality. The idea is simply, when someone has memorized some verses, they can use the quiz feature to go through a bunch of them and make sure they remember what the memorized. Using the existing memorization tool is fine, but we want them to be in rapid succession, and if they choose to do 5 verses for example, it should show "X of 5" at the top. Their current score/correct percentage as well. Give them a grade at the end, and record their quiz. Make sure this is part of the audit log, and make sure we have tests for it too.

[ ] Copilot Task: Convert Our Laravel + Livewire App into a Progressive Web App (PWA)

You are an AI coding assistant working in a Laravel 12 + Vite + Livewire 3 project (no Filament). Implement a production‑ready PWA with installability, an offline fallback page, safe caching for assets/pages, and **no caching of Livewire/auth endpoints**.

Follow the instructions precisely, make minimal, idempotent changes, and open a PR when done. Include commit messages and a verification checklist in the PR description.

---

## Goals / Acceptance Criteria

1. App is **installable** (has a valid `manifest.webmanifest`, icons, `display: standalone`, `start_url` `/`).
2. A **service worker** (SW) is registered via **vite-plugin-pwa** with `registerType: 'autoUpdate'`.
3. **Offline**: visiting any route while offline shows `/offline` fallback; previously visited pages/assets load from cache.
4. **Correct caching rules**:

   * **NetworkOnly** for: `/livewire/*` (all), `/login`, `/logout`, `/register`, `/password/*`, `/sanctum/csrf-cookie`, `/broadcasting/*`.
   * **NetworkFirst** for HTML navigations.
   * **StaleWhileRevalidate** for JS/CSS/workers and images.
   * **CacheFirst** for Google Fonts.
5. iOS install experience: `apple-touch-icon`, `apple-mobile-web-app-capable=yes`, `theme-color` meta.
6. Lighthouse PWA audit passes “Installable” and “PWA Optimized”.

---

## Assumptions

* Repo uses **Vite** and an app entry at `resources/js/app.js` (adjust if different).
* Blade base layout is at `resources/views/layouts/app.blade.php` (adjust if different). If multiple layouts, update the public‑facing one.
* Public web root is `public/`.

---

## Step 1 — Add the PWA dependency

Run:

```bash
npm i -D vite-plugin-pwa
```

---

## Step 2 — Configure Vite for PWA

**Create or update** `vite.config.ts` (or `vite.config.js` if the repo uses JS) with the following. If a config already exists, merge carefully; do not remove existing plugin options.

```ts
// vite.config.ts
import { defineConfig } from 'vite'
import laravel from 'laravel-vite-plugin'
import { VitePWA } from 'vite-plugin-pwa'

export default defineConfig({
  plugins: [
    laravel({
      input: [
        'resources/css/app.css',
        'resources/js/app.js',
      ],
      refresh: true,
    }),
    VitePWA({
      registerType: 'autoUpdate',
      injectRegister: 'auto',
      includeAssets: ['favicon.ico', 'robots.txt', 'apple-touch-icon.png'],
      manifest: {
        name: 'APP_NAME',
        short_name: 'APP_SHORT',
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
})
```

> Replace `APP_NAME` and `APP_SHORT` with the real app name/short name.

---

## Step 3 — Register the Service Worker in the JS entry

Create a small module to register the SW and wire up update/offline events.

**Create** `resources/js/pwa.js`:

```js
import { registerSW } from 'virtual:pwa-register'

// Expose a simple lifecycle so we can show a refresh prompt if desired
const updateSW = registerSW({
  immediate: true,
  onNeedRefresh() {
    // Dispatch a browser event so Blade/Livewire/Alpine can hook into it
    window.dispatchEvent(new CustomEvent('sw:need-refresh'))
  },
  onOfflineReady() {
    window.dispatchEvent(new CustomEvent('sw:offline-ready'))
  },
})

// Optional: re-export updater for manual control if needed elsewhere
export { updateSW }
```

**Update** `resources/js/app.js` to import this module **once** (top‑level):

```js
import './pwa'
```

---

## Step 4 — Update the base Blade layout

Edit `resources/views/layouts/app.blade.php` (adjust if different):

1. Ensure Vite assets and Livewire directives remain intact.
2. Add manifest & theme color links.
3. Add iOS meta & touch icon.

```blade
{{-- In <head> --}}
@vite(['resources/css/app.css', 'resources/js/app.js'])
<link rel="manifest" href="/manifest.webmanifest">
<meta name="theme-color" content="#111827">
<meta name="apple-mobile-web-app-capable" content="yes">
<meta name="apple-mobile-web-app-status-bar-style" content="default">
<link rel="apple-touch-icon" href="/apple-touch-icon.png">
```

> Do **not** add any special script tags for the SW; vite-plugin-pwa handles injection.

Optionally, add a minimal prompt for updates (e.g., in a small Blade partial loaded globally):

```blade
<script>
  window.addEventListener('sw:need-refresh', () => {
    if (confirm('A new version is available. Refresh now?')) location.reload();
  });
  window.addEventListener('sw:offline-ready', () => console.log('App ready to work offline.'))
</script>
```

---

## Step 5 — Add an Offline Fallback Route & View

**Route**:

```php
// routes/web.php
Route::view('/offline', 'offline')->name('offline');
```

**View**:

```blade
{{-- resources/views/offline.blade.php --}}
<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>Offline</title>
  <style>body{font-family:system-ui;margin:2rem} .card{max-width:36rem}</style>
</head>
<body>
  <div class="card">
    <h1>You’re offline</h1>
    <p>This page is always available without internet. Other pages will load when you’re back online.</p>
  </div>
</body>
</html>
```

---

## Step 6 — Provide App Icons

Place these files in `public/`:

* `apple-touch-icon.png` (180×180)
* `icons/pwa-192x192.png`
* `icons/pwa-512x512.png`
* `icons/maskable-512.png` (512×512 with safe padding)

If icons don’t exist, generate placeholders (simple solid color + logo glyph) so Lighthouse passes.

---

## Step 7 — Build, Deploy & Verify

Run locally:

```bash
npm run build
php artisan optimize:clear
```

Verification steps (local or staging over HTTPS):

1. Open DevTools → **Application → Manifest**: verify fields and icons.
2. Application → **Service Workers**: verify SW is **activated** and **claiming clients**.
3. Network → set **Offline**, reload → see the `/offline` page and previously cached assets/pages.
4. Run **Lighthouse → PWA**: ensure “Installable” + “PWA Optimized” pass.
5. On Android/Chrome: confirm “Install app” prompt and app launches standalone.

---

## Notes / Pitfalls

* Do **not** cache any Livewire XHR/POST endpoints; the regex rules above exclude them.
* Avoid caching auth/session endpoints; they’re excluded.
* If you have a separate admin area or broadcasting dashboards, either exclude or ensure they’re NetworkOnly.
* The SW is disabled in dev by default; use `devOptions.enabled=true` only if you know what you’re doing.

---

## Optional Enhancements (Stretch)

* **In‑app Update Toast**: replace the `confirm()` with a custom UI (Alpine/Livewire component) listening for `sw:need-refresh` and calling `location.reload()`.
* **Background Sync / queued actions**: future iteration; would need IndexedDB + replay logic for Livewire actions.
* **Web Push**: add a `push` listener in the SW and integrate Laravel Web Push (VAPID) later.

---

## File Diff Summary (what this task should add/modify)

* `package.json`: add `vite-plugin-pwa` devDependency.
* `vite.config.ts` (or `.js`): add `VitePWA(...)` plugin config as above.
* `resources/js/pwa.js`: **new**.
* `resources/js/app.js`: import `./pwa` (one line).
* `resources/views/layouts/app.blade.php`: add manifest/theme/iOS meta tags.
* `routes/web.php`: add `/offline` route.
* `resources/views/offline.blade.php`: **new**.
* `public/apple-touch-icon.png`: **new**.
* `public/icons/pwa-192x192.png`: **new**.
* `public/icons/pwa-512x512.png`: **new**.
* `public/icons/maskable-512.png`: **new**.

---

## Commit Messages

Use two commits:

1. `chore(pwa): add vite-plugin-pwa config, SW registration, offline page`
2. `feat(pwa): add icons and iOS meta; tune caching rules`

---

## PR Checklist (auto‑include in PR description)

* [ ] `manifest.webmanifest` loads and has correct name/icons/colors
* [ ] Service worker is registered, activated, and `clientsClaim` works
* [ ] Offline mode shows `/offline` and cached pages/assets
* [ ] Livewire and auth endpoints are **never** cached
* [ ] Lighthouse PWA audit passes
* [ ] iOS install tested (add to Home Screen shows proper icon)

---

## After Merge

* Rebuild assets & deploy: `npm run build` and normal deployment flow.
* Bust old caches by virtue of `autoUpdate` + new asset hashes; users will get updates on next visit.

---

**End of task.** Make the changes, then open a PR with screenshots of DevTools (Manifest & SW panels) and Lighthouse PWA results.
