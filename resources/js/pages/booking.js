
export default function staffBooking() {
    return {
        config: {
            services: [],
            factors: [],
            urls: {},
        },

        services: [],
        factors: [],

        customerQuery: '',
        customerResults: [],
        selectedCustomer: null,
        customerName: '',
        customerPhone: '',
        searchMessage: '',

        pet: {
            nama: '',
            spesies: '',
            ras: '',
            ukuran: '',
            jenis_bulu: '',
            perilaku: '',
            alergi: '',
            catatan_kesehatan: '',
        },

        conditions: [],
        selectedServices: [],

        estimation: null,
        loadingEstimation: false,

        bookingDate: '',
        today: '',
        slots: [],
        selectedSlot: null,
        loadingSlots: false,
        slotMessage: '',

        customerNote: '',
        errorMessage: '',
        submitting: false,
        bookingResult: null,

        get totalPrice() {
            return this.services
                .filter(service =>
                    this.selectedServices.includes(Number(service.id))
                )
                .reduce((total, service) =>
                    total + Number(service.harga || 0), 0
                );
        },

        init() {
            const configElement = this.$el.querySelector(
                '[data-booking-config]'
            );

            if (!configElement) {
                this.errorMessage = 'Konfigurasi booking tidak ditemukan.';
                return;
            }

            try {
                this.config = JSON.parse(configElement.textContent);
                this.services = this.config.services || [];
                this.factors = this.config.factors || [];
            } catch (error) {
                console.error('Booking configuration error:', error);
                this.errorMessage = 'Gagal memuat konfigurasi booking.';
            }

            const now = new Date();

            this.today = [
                now.getFullYear(),
                String(now.getMonth() + 1).padStart(2, '0'),
                String(now.getDate()).padStart(2, '0'),
            ].join('-');

            this.bookingDate = this.today;
        },

        factorsByCategory(category) {
            return this.factors.filter(
                factor => factor.kategori === category
            );
        },

        async request(url, options = {}) {
            const token = document.querySelector(
                'meta[name="csrf-token"]'
            )?.content;

            const response = await fetch(url, {
                ...options,
                headers: {
                    Accept: 'application/json',
                    'Content-Type': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest',
                    ...(token ? { 'X-CSRF-TOKEN': token } : {}),
                    ...(options.headers || {}),
                },
            });

            const data = await response.json().catch(() => ({}));

            if (!response.ok) {
                const message =
                    data.message ||
                    Object.values(data.errors || {}).flat()[0] ||
                    'Terjadi kesalahan pada server.';

                throw new Error(message);
            }

            return data;
        },

        async searchCustomers() {
            this.customerResults = [];
            this.searchMessage = '';

            const query = this.customerQuery.trim();

            if (query.length < 2) {
                return;
            }

            try {
                const url = new URL(
                    this.config.urls.pelanggan,
                    window.location.origin
                );

                url.searchParams.set('q', query);

                const result = await this.request(url.toString(), {
                    method: 'GET',
                    headers: {
                        'Content-Type': 'application/json',
                    },
                });

                this.customerResults = result.data || [];

                if (this.customerResults.length === 0) {
                    this.searchMessage =
                        'Pelanggan tidak ditemukan. Silakan masukkan data baru.';
                }
            } catch (error) {
                this.searchMessage = error.message;
            }
        },

        selectCustomer(customer) {
            this.selectedCustomer = customer;
            this.customerName = customer.nama;
            this.customerPhone = customer.no_wa;
            this.customerQuery = customer.nama;
            this.customerResults = [];
            this.searchMessage = '';
        },

        clearCustomer() {
            this.selectedCustomer = null;
            this.customerQuery = '';
            this.customerName = '';
            this.customerPhone = '';
            this.customerResults = [];
        },

        petPayload() {
            return {
                spesies: this.pet.spesies,
                ukuran: this.pet.ukuran,
                jenis_bulu: this.pet.jenis_bulu,
                perilaku: this.pet.perilaku,
            };
        },

        validatePet() {
            const required = [
                ['nama', 'Nama hewan'],
                ['spesies', 'Spesies'],
                ['ukuran', 'Ukuran'],
                ['jenis_bulu', 'Jenis bulu'],
                ['perilaku', 'Perilaku'],
            ];

            for (const [field, label] of required) {
                if (!String(this.pet[field] || '').trim()) {
                    throw new Error(`${label} wajib diisi.`);
                }
            }
        },

        validateServices() {
            if (this.selectedServices.length === 0) {
                throw new Error('Pilih minimal satu layanan.');
            }
        },

        resetEstimation() {
            this.estimation = null;
            this.slots = [];
            this.selectedSlot = null;
            this.slotMessage = '';
        },

        async calculateDuration() {
            this.errorMessage = '';

            try {
                this.validatePet();
                this.validateServices();

                this.loadingEstimation = true;
                this.resetEstimation();

                const result = await this.request(
                    this.config.urls.estimasi,
                    {
                        method: 'POST',
                        body: JSON.stringify({
                            pet: this.petPayload(),
                            service_ids: this.selectedServices,
                            kondisi: this.conditions,
                        }),
                    }
                );

                this.estimation = result;

            } catch (error) {
                this.errorMessage = error.message;
            } finally {
                this.loadingEstimation = false;
            }
        },

        async findSlots() {
            this.errorMessage = '';
            this.slotMessage = '';
            this.slots = [];
            this.selectedSlot = null;

            if (!this.bookingDate) {
                return;
            }

            try {
                this.validatePet();
                this.validateServices();

                this.loadingSlots = true;

                const result = await this.request(
                    this.config.urls.slot,
                    {
                        method: 'POST',
                        body: JSON.stringify({
                            pet: this.petPayload(),
                            service_ids: this.selectedServices,
                            kondisi: this.conditions,
                            tanggal: this.bookingDate,
                        }),
                    }
                );

                this.estimation = {
                    durasi_menit: result.durasi_menit,
                };

                this.slots = result.slots || [];

                if (this.slots.length === 0) {
                    this.slotMessage =
                        result.alasan ||
                        'Tidak ada slot tersedia pada tanggal ini.';
                }

            } catch (error) {
                this.slotMessage = error.message;
            } finally {
                this.loadingSlots = false;
            }
        },

        selectSlot(slot) {
            this.selectedSlot = slot;
            this.errorMessage = '';
        },

        formatTime(value) {
            if (!value) return '-';

            const date = new Date(value.replace(' ', 'T'));

            return date.toLocaleTimeString('id-ID', {
                hour: '2-digit',
                minute: '2-digit',
                hour12: false,
                timeZone: 'Asia/Jakarta',
            });
        },

        formatCurrency(value) {
            return new Intl.NumberFormat('id-ID', {
                style: 'currency',
                currency: 'IDR',
                maximumFractionDigits: 0,
            }).format(Number(value || 0));
        },

        async submitBooking() {
            this.errorMessage = '';

            try {
                if (!this.selectedCustomer) {
                    if (!this.customerName.trim()) {
                        throw new Error('Nama pelanggan wajib diisi.');
                    }

                    if (!this.customerPhone.trim()) {
                        throw new Error('Nomor WhatsApp wajib diisi.');
                    }
                }

                this.validatePet();
                this.validateServices();

                if (!this.selectedSlot) {
                    throw new Error('Pilih slot booking terlebih dahulu.');
                }

                if (!this.estimation) {
                    throw new Error(
                        'Hitung estimasi durasi sebelum konfirmasi booking.'
                    );
                }

                this.submitting = true;

                const payload = {
                    no_wa: this.selectedCustomer
                        ? this.selectedCustomer.no_wa
                        : this.customerPhone,

                    nama_pelanggan: this.selectedCustomer
                        ? null
                        : this.customerName,

                    customer_id: this.selectedCustomer
                        ? this.selectedCustomer.id
                        : null,

                    pet: {
                        ...this.pet,
                    },

                    service_ids: this.selectedServices,
                    kondisi: this.conditions,

                    tanggal: this.bookingDate,
                    mulai: this.selectedSlot.mulai,

                    catatan_pelanggan: this.customerNote,

                    idempotency_key: this.createIdempotencyKey(),
                };

                const result = await this.request(
                    this.config.urls.konfirmasi,
                    {
                        method: 'POST',
                        body: JSON.stringify(payload),
                    }
                );

                this.bookingResult = result.booking;

            } catch (error) {
                this.errorMessage = error.message;
            } finally {
                this.submitting = false;
            }
        },

        createIdempotencyKey() {
            if (window.crypto?.randomUUID) {
                return window.crypto.randomUUID().replaceAll('-', '');
            }

            return (
                Date.now().toString(36) +
                Math.random().toString(36).slice(2) +
                Math.random().toString(36).slice(2)
            ).slice(0, 64);
        },
    };
}
