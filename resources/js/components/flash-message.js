/** Notifikasi yang dapat ditutup manual atau otomatis. timeout 0 berarti tidak otomatis. */
export default (timeout = 0) => ({
    show: true,
    leaving: false,

    init() {
        if (timeout > 0) {
            setTimeout(() => this.dismiss(), timeout);
        }
    },

    dismiss() {
        this.leaving = true;
        setTimeout(() => { this.show = false; }, 180);
    },
});
