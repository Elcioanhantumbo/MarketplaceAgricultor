// Service worker mínimo (secção 19/20): só armazena em cache os ficheiros
// estáticos compilados (nomes com hash, seguros para cache indefinida).
// Páginas, formulários e chamadas Livewire vão sempre à rede — nada de
// operações críticas (pedidos, pagamentos, confirmações) serve versões
// antigas offline.
const CACHE_NAME = 'agrolink-static-v1';

self.addEventListener('install', (event) => {
    self.skipWaiting();
});

self.addEventListener('activate', (event) => {
    event.waitUntil(
        caches.keys().then((keys) => Promise.all(
            keys.filter((key) => key !== CACHE_NAME).map((key) => caches.delete(key)),
        )),
    );
    self.clients.claim();
});

self.addEventListener('fetch', (event) => {
    const { request } = event;

    if (request.method !== 'GET' || !request.url.includes('/build/')) {
        return;
    }

    event.respondWith(
        caches.match(request).then((cached) => {
            if (cached) return cached;

            return fetch(request).then((response) => {
                const copy = response.clone();
                caches.open(CACHE_NAME).then((cache) => cache.put(request, copy));

                return response;
            });
        }),
    );
});