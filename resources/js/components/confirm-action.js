/** Konfirmasi form menggunakan elemen <dialog> native. */
export default () => ({
    ask() {
        const dialog = this.$refs.dialog;
        if (dialog && typeof dialog.showModal === 'function') {
            dialog.showModal();
        } else if (window.confirm(this.$refs.dialog?.dataset.fallback || 'Lanjutkan?')) {
            this.send();
        }
    },

    cancel() {
        this.$refs.dialog?.close();
    },

    send() {
        this.$refs.dialog?.close();
        this.$root.submit();
    },

    backdrop(event) {
        if (event.target === this.$refs.dialog) this.cancel();
    },
});
