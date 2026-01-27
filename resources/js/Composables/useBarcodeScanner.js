import { ref, onMounted, onUnmounted } from 'vue';

/**
 * useBarcodeScanner - Composable for handling barcode scanner input
 *
 * Barcode scanners typically type characters rapidly followed by Enter.
 * This composable detects that pattern and triggers a callback.
 *
 * @param {function} onScan - Callback when barcode is scanned
 * @param {object} options - Configuration options
 * @param {number} options.minLength - Minimum barcode length (default: 3)
 * @param {number} options.maxDelay - Max delay between keystrokes in ms (default: 50)
 * @param {boolean} options.enabled - Enable/disable scanner (default: true)
 * @param {string[]} options.ignoreInputs - Input types to ignore (default: ['text', 'number', 'search'])
 *
 * @returns {object} { lastBarcode, isScanning, reset }
 */
export default function useBarcodeScanner(onScan, options = {}) {
    const {
        minLength = 3,
        maxDelay = 50,
        enabled = true,
        ignoreInputs = ['text', 'number', 'search', 'password'],
    } = options;

    const lastBarcode = ref('');
    const isScanning = ref(false);

    const buffer = ref('');
    const lastKeyTime = ref(0);
    let timeoutId = null;

    const reset = () => {
        buffer.value = '';
        isScanning.value = false;
    };

    const handleKeyDown = (e) => {
        if (!enabled) return;

        // Ignore if typing in input fields
        const activeElement = document.activeElement;
        if (activeElement) {
            const tagName = activeElement.tagName.toLowerCase();
            const inputType = activeElement
                .getAttribute('type')
                ?.toLowerCase();

            if (tagName === 'textarea') return;
            if (
                tagName === 'input' &&
                ignoreInputs.includes(inputType || 'text')
            )
                return;
        }

        const now = Date.now();
        const timeSinceLastKey = now - lastKeyTime.value;

        // If too much time has passed, reset buffer
        if (timeSinceLastKey > maxDelay && buffer.value.length > 0) {
            buffer.value = '';
        }

        lastKeyTime.value = now;

        // Handle Enter key - end of barcode
        if (e.key === 'Enter') {
            if (buffer.value.length >= minLength) {
                const barcode = buffer.value;
                lastBarcode.value = barcode;
                isScanning.value = false;
                onScan?.(barcode);
            }
            buffer.value = '';
            return;
        }

        // Only accept alphanumeric characters and common barcode chars
        if (e.key.length === 1 && /^[a-zA-Z0-9\-_.]$/.test(e.key)) {
            buffer.value += e.key;
            isScanning.value = true;

            // Clear timeout and set new one
            if (timeoutId) {
                clearTimeout(timeoutId);
            }

            timeoutId = setTimeout(() => {
                // Reset if no more input
                buffer.value = '';
                isScanning.value = false;
            }, maxDelay * 3);
        }
    };

    onMounted(() => {
        if (!enabled) return;
        window.addEventListener('keydown', handleKeyDown);
    });

    onUnmounted(() => {
        window.removeEventListener('keydown', handleKeyDown);
        if (timeoutId) {
            clearTimeout(timeoutId);
        }
    });

    return {
        lastBarcode,
        isScanning,
        reset,
    };
}

