/** Menu pengguna: buka/tutup, tutup dengan klik di luar atau Escape melalui binding Blade. */
export default () => ({
    open: false,
    toggle() { this.open = !this.open; },
    close() { this.open = false; },
});
