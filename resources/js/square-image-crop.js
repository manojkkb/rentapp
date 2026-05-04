/**
 * Reusable square WebP crop (Croppie) for vendor uploads.
 */
import CroppieImport from 'croppie';
import 'croppie/croppie.css';

const Croppie = CroppieImport && (CroppieImport.default || CroppieImport);

const WEBP_QUALITY = 0.92;

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

/** Fallback when browsers drop `input.files` (e.g. file input inside Alpine x-show / display:none). */
function cropBlobStore() {
    if (! window.__squareCropLastByInput) {
        window.__squareCropLastByInput = new WeakMap();
    }

    return window.__squareCropLastByInput;
}

function rememberSquareCropOutput(inputEl, file) {
    cropBlobStore().set(inputEl, file);
}

function forgetSquareCropOutput(inputEl) {
    if (! inputEl) {
        return;
    }
    cropBlobStore().delete(inputEl);
}

if (typeof window !== 'undefined') {
    window.__squareCropGetLastBlob = (input) => (input ? cropBlobStore().get(input) : null);
    window.__squareCropForgetLastBlob = forgetSquareCropOutput;
}

/**
 * @param {object} cfg
 * @param {string} cfg.bootKey — unique window flag name
 * @param {string} cfg.modalId
 * @param {string} cfg.stageId
 * @param {string} cfg.applyId
 * @param {string} [cfg.applyLabelId]
 * @param {string} cfg.inputClass — class on file input (no dot)
 * @param {string} cfg.cancelClass — cancel buttons (no dot)
 * @param {string} cfg.backdropClass — backdrop (no dot)
 * @param {string} cfg.croppieRootClass — Croppie customClass
 * @param {string} cfg.filePrefix — blob filename prefix
 * @param {string} cfg.logTag — console prefix e.g. [item-image-crop]
 * @param {number} [cfg.outputSize=512] — width/height of exported square image
 */
export function initSquareImageCrop(cfg) {
    const {
        bootKey,
        modalId,
        stageId,
        applyId,
        applyLabelId,
        inputClass,
        cancelClass,
        backdropClass,
        croppieRootClass,
        filePrefix,
        logTag,
        outputSize = 512,
    } = cfg;

    if (window[bootKey]) {
        return;
    }

    const modal = document.getElementById(modalId);
    const stage = document.getElementById(stageId);
    const btnApply = document.getElementById(applyId);
    const applyLabel = applyLabelId ? document.getElementById(applyLabelId) : null;
    const applyLabelDefault = applyLabel ? applyLabel.textContent.trim() : 'Use this image';

    if (! modal || ! stage || ! btnApply) {
        return;
    }

    window[bootKey] = true;

    if (typeof Croppie !== 'function') {
        console.error(logTag, 'Croppie is not a constructor', Croppie);

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
            applyLabel.textContent = applyLabelDefault;
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
                    customClass: croppieRootClass,
                });
            } catch (err) {
                console.error(logTag, 'Croppie init', err);
                closeModal();

                return;
            }

            const bindResult = croppieInstance.bind({ url: objectUrl });

            if (bindResult && typeof bindResult.then === 'function') {
                bindResult.catch((err) => {
                    console.error(logTag, 'bind', err);
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

    document.querySelectorAll('.' + cancelClass).forEach((btn) => {
        btn.addEventListener('click', () => {
            if (activeInput) {
                forgetSquareCropOutput(activeInput);
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
            format: 'webp',
            quality: WEBP_QUALITY,
            size: { width: outputSize, height: outputSize },
            circle: false,
        });

        const resultPromise = result && typeof result.then === 'function' ? result : Promise.resolve(result);

        resultPromise
            .then((blob) => {
                if (! blob || ! activeInput) {
                    closeModal();

                    return;
                }

                const name = filePrefix + '-' + Date.now() + '.webp';
                const outFile = new File([blob], name, { type: 'image/webp' });
                const dataTransfer = new DataTransfer();
                dataTransfer.items.add(outFile);
                activeInput.files = dataTransfer.files;
                rememberSquareCropOutput(activeInput, outFile);
                closeModal();
            })
            .catch((err) => {
                console.error(logTag, 'result', err);
                btnApply.disabled = false;
                if (applyLabel) {
                    applyLabel.textContent = applyLabelDefault;
                }
            });
    });

    modal.addEventListener('click', (e) => {
        if (e.target.classList.contains(backdropClass)) {
            if (activeInput) {
                forgetSquareCropOutput(activeInput);
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
        if (! el.classList || ! el.classList.contains(inputClass)) {
            return;
        }

        const file = el.files && el.files[0];
        if (! file || ! isImageFile(file)) {
            return;
        }

        const inputEl = el;
        const picked = file;

        forgetSquareCropOutput(inputEl);
        inputEl.value = '';

        openModal(picked, inputEl);
    });
}

export function bootSquareImageCrops() {
    initSquareImageCrop({
        bootKey: '__categoryImageCropBooted',
        modalId: 'categoryImageCropModal',
        stageId: 'categoryImageCropStage',
        applyId: 'categoryImageCropApply',
        applyLabelId: 'categoryImageCropApplyLabel',
        inputClass: 'js-category-image-input',
        cancelClass: 'js-category-crop-cancel',
        backdropClass: 'js-category-crop-backdrop',
        croppieRootClass: 'category-croppie-root',
        filePrefix: 'category',
        logTag: '[category-image-crop]',
        outputSize: 512,
    });

    initSquareImageCrop({
        bootKey: '__itemImageCropBooted',
        modalId: 'itemImageCropModal',
        stageId: 'itemImageCropStage',
        applyId: 'itemImageCropApply',
        applyLabelId: 'itemImageCropApplyLabel',
        inputClass: 'js-item-image-input',
        cancelClass: 'js-item-crop-cancel',
        backdropClass: 'js-item-crop-backdrop',
        croppieRootClass: 'item-croppie-root',
        filePrefix: 'item',
        logTag: '[item-image-crop]',
        outputSize: 1024,
    });

    initSquareImageCrop({
        bootKey: '__vendorLogoCropBooted',
        modalId: 'vendorLogoCropModal',
        stageId: 'vendorLogoCropStage',
        applyId: 'vendorLogoCropApply',
        applyLabelId: 'vendorLogoCropApplyLabel',
        inputClass: 'js-vendor-logo-input',
        cancelClass: 'js-vendor-logo-crop-cancel',
        backdropClass: 'js-vendor-logo-crop-backdrop',
        croppieRootClass: 'vendor-logo-croppie-root',
        filePrefix: 'vendor-logo',
        logTag: '[vendor-logo-crop]',
        outputSize: 512,
    });

    initSquareImageCrop({
        bootKey: '__userAvatarCropBooted',
        modalId: 'userAvatarCropModal',
        stageId: 'userAvatarCropStage',
        applyId: 'userAvatarCropApply',
        applyLabelId: 'userAvatarCropApplyLabel',
        inputClass: 'js-user-avatar-input',
        cancelClass: 'js-user-avatar-crop-cancel',
        backdropClass: 'js-user-avatar-crop-backdrop',
        croppieRootClass: 'user-avatar-croppie-root',
        filePrefix: 'user-avatar',
        logTag: '[user-avatar-crop]',
        outputSize: 512,
    });
}

if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', bootSquareImageCrops);
} else {
    bootSquareImageCrops();
}
