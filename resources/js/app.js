// Secção 12/19 — mapa leve (OpenStreetMap via Leaflet, sem chave de API).
// Carregado sob pedido (import dinâmico) para não pesar nas páginas sem
// mapa — Leaflet só é descarregado quando uma destas páginas é visitada.
async function loadLeaflet() {
    const [{ default: L }] = await Promise.all([
        import('leaflet'),
        import('leaflet/dist/leaflet.css'),
    ]);

    // O bundler não resolve os caminhos relativos por omissão do Leaflet
    // para os ícones dos marcadores — aponta-se para os assets importados.
    const [markerIcon2x, markerIcon, markerShadow] = await Promise.all([
        import('leaflet/dist/images/marker-icon-2x.png').then((m) => m.default),
        import('leaflet/dist/images/marker-icon.png').then((m) => m.default),
        import('leaflet/dist/images/marker-shadow.png').then((m) => m.default),
    ]);

    delete L.Icon.Default.prototype._getIconUrl;
    L.Icon.Default.mergeOptions({
        iconRetinaUrl: markerIcon2x,
        iconUrl: markerIcon,
        shadowUrl: markerShadow,
    });

    return L;
}

document.addEventListener('alpine:init', () => {
    Alpine.data('listingsMap', (markers) => ({
        async init() {
            if (!markers.length) return;
            const L = await loadLeaflet();

            const map = L.map(this.$el).setView([markers[0].lat, markers[0].lng], 9);
            L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                attribution: '&copy; OpenStreetMap',
                maxZoom: 18,
            }).addTo(map);

            const group = L.featureGroup();
            markers.forEach((marker) => {
                L.marker([marker.lat, marker.lng])
                    .bindPopup(marker.popup)
                    .addTo(group);
            });
            group.addTo(map);

            if (markers.length > 1) {
                map.fitBounds(group.getBounds().pad(0.2));
            }
        },
    }));

    Alpine.data('singleLocationMap', (lat, lng, label) => ({
        async init() {
            const L = await loadLeaflet();

            const map = L.map(this.$el, { scrollWheelZoom: false }).setView([lat, lng], 12);
            L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                attribution: '&copy; OpenStreetMap',
                maxZoom: 18,
            }).addTo(map);
            L.marker([lat, lng]).addTo(map).bindPopup(label).openPopup();
        },
    }));
});

// Secção 19 — compressão de imagem no navegador antes do envio, para poupar
// dados em ligações lentas (2G/3G).
document.addEventListener('alpine:init', () => {
    Alpine.data('avatarUploader', () => ({
        preview: null,
        compressing: false,

        handleFile(event) {
            const file = event.target.files[0];
            if (!file) return;

            this.compressing = true;
            const reader = new FileReader();

            reader.onload = (readerEvent) => {
                const img = new Image();

                img.onload = () => {
                    const maxDimension = 800;
                    let { width, height } = img;

                    if (width > height && width > maxDimension) {
                        height = Math.round(height * (maxDimension / width));
                        width = maxDimension;
                    } else if (height > maxDimension) {
                        width = Math.round(width * (maxDimension / height));
                        height = maxDimension;
                    }

                    const canvas = document.createElement('canvas');
                    canvas.width = width;
                    canvas.height = height;
                    canvas.getContext('2d').drawImage(img, 0, 0, width, height);

                    canvas.toBlob((blob) => {
                        const compressed = new File(
                            [blob],
                            file.name.replace(/\.[^.]+$/, '.jpg'),
                            { type: 'image/jpeg' },
                        );

                        this.preview = URL.createObjectURL(compressed);

                        this.$wire.upload(
                            'avatar',
                            compressed,
                            () => { this.compressing = false; },
                            () => { this.compressing = false; },
                        );
                    }, 'image/jpeg', 0.75);
                };

                img.src = readerEvent.target.result;
            };

            reader.readAsDataURL(file);
        },
    }));
});

// PWA — regista o service worker do "app shell" (secção 19/20), se suportado.
if ('serviceWorker' in navigator) {
    window.addEventListener('load', () => {
        navigator.serviceWorker.register('/sw.js').catch(() => {});
    });
}