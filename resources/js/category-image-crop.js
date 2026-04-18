/**
 * Category image — square crop (Croppie).
 * Bundled from app.js so it always runs on vendor pages (no separate @vite entry).
 */
import CroppieImport from 'croppie';
import 'croppie/croppie.css';

const Croppie = CroppieImport && (CroppieImport.default || CroppieImport);

const OUTPUT_SIZE = 512;
const JPEG_QUALITY = 0.92;

function viewportHeight() {
    if (typeof window !== 'undefined' && window.visualViewport) {
        return window.visualViewport.height;
    }

    return window.innerHeight || document.documentElement.clientHeight || 600;
}

function viewportWidth() {
    return window.innerWidth || document.documentElement.clientWidth || 360;
}

function isImageFile(file) {
    if (! file || ! file.type) {
        return true;
    }

    return file.type.startsWith('image/');
}

function boot() {
    if (window.__categoryImageCropBooted) {
        return;
    }

    const modal = document.getElementById('categoryImageCropModal');
    const stage = document.getElementById('categoryImageCropStage');
    const btnApply = document.getElementById('categoryImageCropApply');
    const applyLabel = document.getElementById('categoryImageCropApplyLabel');

    if (! modal || ! stage || ! btnApply) {
        return;
    }

    window.__categoryImageCropBooted = true;

    if (typeof Croppie !== 'function') {
        console.error('[category-image-crop] Croppie is not a constructor', Croppie);

        return;
    }

    let croppieInstance = null;
    let objectUrl = null;
    let activeInput = null;

    function closeModal() {
        if (croppieInstance) {
            try {
                croppieInstance.destroy();
            } catch (e) {
                /* ignore */
            }
            croppieInstance = null;
        }
        if (objectUrl) {
            URL.revokeObjectURL(objectUrl);
            objectUrl = null;
        }
        stage.innerHTML = '';
        stage.style.minWidth = '';
        stage.style.minHeight = '';
        stage.style.width = '';
        stage.style.height = '';
        stage.style.maxWidth = '';
        btnApply.disabled = false;
        if (applyLabel) {
            applyLabel.textContent = 'Use this image';
        }
        modal.classList.add('hidden');
        document.body.classList.remove('overflow-hidden');
        activeInput = null;
    }

    function measureStage() {
        void modal.offsetHeight;
        void stage.offsetHeight;

        const rect = stage.getBoundingClientRect();
        let w = Math.floor(rect.width);
        let h = Math.floor(rect.height);

        const vw = Math.max(280, Math.min(viewportWidth() - 24, 900));
        const vhRaw = viewportHeight();
        const vh = Math.max(240, Math.min(vhRaw * 0.52, 620));

        if (w < 120 || h < 120) {
            w = vw;
            h = vh;
            stage.style.width = `${w}px`;
            stage.style.maxWidth = '100%';
            stage.style.height = `${h}px`;
            stage.style.minHeight = `${h}px`;
        }

        w = Math.max(200, Math.min(w, vw));
        h = Math.max(200, Math.min(h, vh));

        return { w, h };
    }

    function openModal(file, inputEl) {
        activeInput = inputEl;
        objectUrl = URL.createObjectURL(file);
        modal.classList.remove('hidden');
        document.body.classList.add('overflow-hidden');
        stage.innerHTML = '';

        const runInit = () => {
            void modal.offsetHeight;

            const { w, h } = measureStage();
            const minSide = Math.min(w, h);
            const maxViewport = Math.max(100, Math.floor(minSide) - 8);

            let viewportSize = Math.floor(minSide * 0.72);
            viewportSize = Math.min(Math.max(viewportSize, 120), maxViewport);

            if (viewportSize > maxViewport) {
                viewportSize = maxViewport;
            }

            try {
                croppieInstance = new Croppie(stage, {
                    viewport: { width: viewportSize, height: viewportSize, type: 'square' },
                    boundary: { width: w, height: h },
                    showZoomer: true,
                    enableZoom: true,
                    mouseWheelZoom: true,
                    enableOrientation: false,
                    enableExif: false,
                    enforceBoundary: true,
                    customClass: 'category-croppie-root',
                });
            } catch (err) {
                console.error('[category-image-crop] Croppie init', err);
                closeModal();

                return;
            }

            const bindResult = croppieInstance.bind({ url: objectUrl });

            if (bindResult && typeof bindResult.then === 'function') {
                bindResult.catch((err) => {
                    console.error('[category-image-crop] bind', err);
                    closeModal();
                });
            }
        };

        requestAnimationFrame(() => {
            requestAnimationFrame(() => {
                setTimeout(runInit, 100);
            });
        });
    }

    document.querySelectorAll('.js-category-crop-cancel').forEach((btn) => {
        btn.addEventListener('click', () => {
            if (activeInput) {
                activeInput.value = '';
            }
            closeModal();
        });
    });

    btnApply.addEventListener('click', () => {
        if (! croppieInstance || ! activeInput) {
            closeModal();

            return;
        }

        btnApply.disabled = true;
        if (applyLabel) {
            applyLabel.textContent = 'Processing…';
        }

        const result = croppieInstance.result({
            type: 'blob',
            format: 'jpeg',
            quality: JPEG_QUALITY,
            size: { width: OUTPUT_SIZE, height: OUTPUT_SIZE },
            circle: false,
        });

        const resultPromise = result && typeof result.then === 'function' ? result : Promise.resolve(result);

        resultPromise
            .then((blob) => {
                if (! blob || ! activeInput) {
                    closeModal();

                    return;
                }

                const name = 'category-'.concat(Date.now(), '.jpg');
                const outFile = new File([blob], name, { type: 'image/jpeg' });
                const dataTransfer = new DataTransfer();
                dataTransfer.items.add(outFile);
                activeInput.files = dataTransfer.files;
                closeModal();
            })
            .catch((err) => {
                console.error('[category-image-crop] result', err);
                btnApply.disabled = false;
                if (applyLabel) {
                    applyLabel.textContent = 'Use this image';
                }
            });
    });

    modal.addEventListener('click', (e) => {
        if (e.target.classList.contains('js-category-crop-backdrop')) {
            if (activeInput) {
                activeInput.value = '';
            }
            closeModal();
        }
    });

    document.addEventListener('change', (e) => {
        const el = e.target;
        if (! el || el.type !== 'file') {
            return;
        }
        if (! el.classList || ! el.classList.contains('js-category-image-input')) {
            return;
        }

        const file = el.files && el.files[0];
        if (! file || ! isImageFile(file)) {
            return;
        }

        const inputEl = el;
        const picked = file;

        inputEl.value = '';

        openModal(picked, inputEl);
    });
}

if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', boot);
} else {
    boot();
}
