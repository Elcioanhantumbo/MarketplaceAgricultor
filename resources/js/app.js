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