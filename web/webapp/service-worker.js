var CACHE_VERSION = 'v2026-02-14-3';
var APP_CACHE = 'datefix-app-' + CACHE_VERSION;
var API_CACHE = 'datefix-api-' + CACHE_VERSION;
var STATIC_ASSETS = [
	'./',
	'./manifest.json',
	'./css/app.css',
	'./css/w3.css',
	'./js/app.js',
	'./images/bg.jpg',
	'./images/logo_50.png',
	'./images/logo_70.png',
	'./images/icon_128.png',
	'./images/icon_144.png',
	'./images/icon_152.png',
	'./images/icon_192.png',
	'./images/icon_256.png',
	'./offline.html'
];

function cacheFirst(request) {
	return caches.match(request).then(function(cached) {
		if (cached) {
			return cached;
		}
		return fetch(request).then(function(response) {
			if (!response || response.status !== 200) {
				return response;
			}
			return caches.open(APP_CACHE).then(function(cache) {
				cache.put(request, response.clone());
				return response;
			});
		});
	});
}

function staleWhileRevalidate(request) {
	return caches.open(API_CACHE).then(function(cache) {
		return cache.match(request).then(function(cached) {
			var fetchPromise = fetch(request)
				.then(function(response) {
					if (response && response.status === 200) {
						cache.put(request, response.clone());
					}
					return response;
				})
				.catch(function() {
					return cached;
				});
			return cached || fetchPromise;
		});
	});
}

self.addEventListener('install', function(event) {
	event.waitUntil(
		 caches.open(APP_CACHE).then(function(cache) {
			 return cache.addAll(STATIC_ASSETS);
		 }).then(function() {
			 return self.skipWaiting();
		 })
	);
});

self.addEventListener('activate', function(event) {
	event.waitUntil(
		 caches.keys().then(function(keys) {
			 return Promise.all(
				 keys.map(function(key) {
					 if (key.indexOf('datefix-app-') === 0 && key !== APP_CACHE) {
						 return caches.delete(key);
					 }
					 if (key.indexOf('datefix-api-') === 0 && key !== API_CACHE) {
						 return caches.delete(key);
					 }
				 })
			 );
		 }).then(function() {
			 return self.clients.claim();
		 })
	);
});

self.addEventListener('fetch', function(event) {
	var request = event.request;
	if (request.method !== 'GET') {
		return;
	}

	var url = new URL(request.url);
	var isSameOrigin = url.origin === self.location.origin;
	var isApiRequest = url.pathname.indexOf('/api/kalender') === 0 || url.pathname.indexOf('/api/detail') === 0 || url.pathname.indexOf('/api/news') === 0;

	if (request.mode === 'navigate') {
		event.respondWith(
			fetch(request)
				.then(function(response) {
					return response;
				})
				.catch(function() {
					return caches.match('./offline.html');
				})
		);
		return;
	}

	if (isSameOrigin && isApiRequest) {
		event.respondWith(staleWhileRevalidate(request));
		return;
	}

	if (isSameOrigin) {
		event.respondWith(cacheFirst(request));
	}
});