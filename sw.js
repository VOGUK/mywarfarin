const CACHE_NAME = 'my-warfarin-v1';
const ASSETS_TO_CACHE = [
    './',
    './index.html',
    './Warfarin.png'
];

// Step 1: Install the Service Worker and cache the core files
self.addEventListener('install', event => {
    event.waitUntil(
        caches.open(CACHE_NAME).then(cache => {
            console.log('Opened cache');
            return cache.addAll(ASSETS_TO_CACHE);
        })
    );
});

// Step 2: Activate and clean up old caches if you ever update the app
self.addEventListener('activate', event => {
    event.waitUntil(
        caches.keys().then(cacheNames => {
            return Promise.all(
                cacheNames.filter(name => name !== CACHE_NAME)
                    .map(name => caches.delete(name))
            );
        })
    );
});

// Step 3: Intercept network requests (Offline Mode)
self.addEventListener('fetch', event => {
    // DO NOT cache database API calls! We always want fresh data.
    if (event.request.url.includes('api.php')) {
        return;
    }

    // For everything else (HTML, images), use the cache first, then the network
    event.respondWith(
        caches.match(event.request).then(cachedResponse => {
            return cachedResponse || fetch(event.request);
        })
    );
});