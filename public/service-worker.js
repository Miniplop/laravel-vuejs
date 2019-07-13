self.addEventListener('install', event => {
  event.waitUntil(async function() {
    const cache =  await caches.open('airbnb');
    await cache.addAll([
      '/'
    ])
  }());
});

self.addEventListener('fetch', function(event) {
  event.respondWith( async function() {
    const cachedResponse = caches.match(event.request);

    return cachedResponse || async function() {
      const networkResponse =  fetch(event.request);
      const cache = await caches.open('airbnb-dynamic');
      event.waitUntil(
        cache.put(event.request, networkResponse.clone())
      );

      return networkResponse;
    }
  }());
});
