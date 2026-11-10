export default function appointmentManager() {
    return {
        busy: false,
        cancelModal: false,
        cancelId: null,
        cancelReason: '',
        message: '',

        async send(url, method, payload = {}) {
            const csrf = document.querySelector('meta[name="csrf-token"]')?.content;
            const response = await fetch(url, {
                method,
                headers: {
                    Accept: 'application/json',
                    'Content-Type': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest',
                    ...(csrf ? { 'X-CSRF-TOKEN': csrf } : {}),
                },
                body: method === 'GET' ? undefined : JSON.stringify(payload),
            });
            const data = await response.json().catch(() => ({}));
            if (!response.ok) {
                throw new Error(data.message || Object.values(data.errors || {}).flat()[0] || 'Permintaan gagal diproses.');
            }
            return data;
        },

        async changeStatus(id, status) {
            const labels = { dikerjakan: 'memulai grooming', selesai: 'menyelesaikan grooming', dijemput: 'menandai hewan sudah dijemput' };
            if (!window.confirm(`Yakin ingin ${labels[status]} untuk appointment #${id}?`)) return;
            await this.perform(`/staf/appointments/${id}/status`, { status }, 'Status berhasil diperbarui.');
        },

        openCancel(id) {
            this.cancelId = id;
            this.cancelReason = '';
            this.cancelModal = true;
        },

        async confirmCancel() {
            if (!this.cancelId) return;
            await this.perform(`/staf/appointments/${this.cancelId}/cancel`, { alasan: this.cancelReason }, 'Appointment berhasil dibatalkan.');
            this.cancelModal = false;
        },

        async perform(url, payload, successMessage) {
            if (this.busy) return;
            this.busy = true;
            this.message = '';
            try {
                const data = await this.send(url, 'PATCH', payload);
                this.message = data.message || successMessage;
                window.setTimeout(() => window.location.reload(), 500);
            } catch (error) {
                this.message = error.message;
            } finally {
                this.busy = false;
            }
        },
    };
}
