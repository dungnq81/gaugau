importScripts("https://storage.googleapis.com/workbox-cdn/releases/7.3.0/workbox-sw.js");let e=workbox.routing.registerRoute,t=workbox.strategies.CacheFirst,n=workbox.cacheableResponse.CacheableResponsePlugin,s=workbox.expiration.ExpirationPlugin;e((function(e){let t=e.request;return"/app/themes/hd/assets/css/.*"===t.destination||"/app/themes/hd/assets/js/.*"===t.destination||"/app/themes/hd/assets/img/.*"===t.destination}),new t({cacheName:"GAUDEV-workbox-cache",plugins:[new n({statuses:[0,200]}),new s({maxEntries:60,maxAgeSeconds:604800})]}));let i=["GAUDEV"];self.addEventListener("install",(function(e){e.waitUntil(caches.open("GAUDEV").then((function(e){return e.addAll(["/"])})))})),self.addEventListener("activate",(function(e){e.waitUntil(caches.keys().then((function(e){return Promise.all(e.map((function(e){if(i.includes(e))return caches.delete(e)})))})).then((function(){})))})),self.addEventListener("fetch",(function(e){e.respondWith(caches.match(e.request).then((function(t){return t||fetch(e.request)})))}));
