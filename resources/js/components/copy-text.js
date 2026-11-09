/** Tombol salin teks, misalnya password sementara. */
import { copyToClipboard } from '../utils/clipboard';

export default () => ({
    copied: false,
    failed: false,

    async copy(text) {
        this.failed = false;
        this.copied = await copyToClipboard(text);
        this.failed = !this.copied;
        setTimeout(() => {
            this.copied = false;
            this.failed = false;
        }, 2000);
    },

    get label() {
        if (this.copied) return 'Tersalin';
        if (this.failed) return 'Gagal, salin manual';
        return 'Salin';
    },
});
