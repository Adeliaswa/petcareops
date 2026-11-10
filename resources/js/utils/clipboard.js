/** Salin teks menggunakan Clipboard API dengan fallback textarea. */
export async function copyToClipboard(text) {
    const value = String(text ?? '');

    if (navigator.clipboard && window.isSecureContext) {
        try {
            await navigator.clipboard.writeText(value);
            return true;
        } catch (_) {
            // Lanjutkan ke fallback.
        }
    }

    const area = document.createElement('textarea');
    area.value = value;
    area.setAttribute('readonly', '');
    area.style.position = 'fixed';
    area.style.opacity = '0';
    document.body.appendChild(area);
    area.select();

    let copied = false;
    try {
        copied = document.execCommand('copy');
    } catch (_) {
        copied = false;
    } finally {
        area.remove();
    }

    return copied;
}
