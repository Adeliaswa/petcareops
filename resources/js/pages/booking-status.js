export default function bookingStatus() {
    return {
        booking: null,
        loading: true,
        error: '',

        async init(url) {
            try {
                const response = await fetch(url, {
                    headers: { Accept: 'application/json' },
                    credentials: 'same-origin',
                });
                const data = await response.json().catch(() => ({}));
                if (!response.ok) throw new Error(data.message || 'Status booking tidak dapat dimuat.');
                this.booking = data.booking || null;
                if (!this.booking) throw new Error('Data booking tidak ditemukan.');
            } catch (error) {
                this.error = error.message || 'Terjadi kesalahan saat memuat status.';
            } finally {
                this.loading = false;
            }
        },

        statusLabel(status) {
            return ({
                menunggu: 'Menunggu konfirmasi',
                dikerjakan: 'Sedang dikerjakan',
                selesai: 'Selesai',
                dijemput: 'Sudah dijemput',
                dibatalkan: 'Dibatalkan',
            })[status] || String(status || 'Tidak diketahui').replaceAll('_', ' ');
        },

        formatDate(value) {
            if (!value) return '-';
            const date = new Date(value.replace(' ', 'T'));
            return new Intl.DateTimeFormat('id-ID', {
                weekday: 'long', day: '2-digit', month: 'long', year: 'numeric',
                timeZone: 'Asia/Jakarta',
            }).format(date);
        },

        formatTime(value) {
            if (!value) return '-';
            const date = new Date(value.replace(' ', 'T'));
            return new Intl.DateTimeFormat('id-ID', {
                hour: '2-digit', minute: '2-digit', hour12: false,
                timeZone: 'Asia/Jakarta',
            }).format(date) + ' WIB';
        },

        formatCurrency(value) {
            return new Intl.NumberFormat('id-ID', {
                style: 'currency', currency: 'IDR', maximumFractionDigits: 0,
            }).format(Number(value || 0));
        },
    };
}
