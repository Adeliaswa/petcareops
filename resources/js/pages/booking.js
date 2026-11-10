export default function staffBooking() {

    return {

        config: {

            services: [],

            factors: [],

            urls: {},

            mode: 'staff',

        },



        services: [],

        factors: [],



        // Data pelanggan

        customerQuery: '',

        customerResults: [],

        selectedCustomer: null,

        customerPets: [],

        selectedPetId: '',

        customerName: '',

        customerPhone: '',

        searchMessage: '',

        customerLookupDone: false,

        customerLookupLoading: false,

        lookupRequestId: 0,



        // Data hewan

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



        // Layanan

        conditions: [],

        selectedServices: [],

        estimation: null,

        loadingEstimation: false,



        // Jadwal

        bookingDate: '',

        today: '',

        slots: [],

        weeklySchedule: [],

        selectedSlot: null,

        loadingSlots: false,

        slotMessage: '',

        slotRequestId: 0,



        // Konfirmasi

        customerNote: '',

        errorMessage: '',

        submitting: false,

        bookingResult: null,
        statusLinkCopied: false,



        // =========================================

        // COMPUTED

        // =========================================



        get isPublicBooking() {

            return this.config.mode === 'public';

        },



        get isNewCustomer() {

            return this.isPublicBooking

                && this.customerLookupDone

                && !this.customerLookupLoading

                && this.customerResults.length === 0

                && !this.selectedCustomer;

        },



        get customerReady() {

            return !this.isPublicBooking

                || !!this.selectedCustomer

                || this.isNewCustomer;

        },



 get totalPrice() {
    return this.services
        .filter(service =>
            this.selectedServices.some(
                id => String(id) === String(service.id)
            )
        )
        .reduce(
            (total, service) =>
                total + Number(service.harga || 0),
            0
        );
},


        get recommendedDays() {
            return this.weeklySchedule
                .filter(day => Array.isArray(day.slots) && day.slots.length > 0)
                .slice()
                .sort((a, b) => b.slots.length - a.slots.length)
                .slice(0, 3)
                .map(day => day.date);
        },

        get selectedServiceNames() {
    return this.services
        .filter(service =>
            this.selectedServices.some(
                id => String(id) === String(service.id)
            )
        )
        .map(service => service.nama);
},

        // =========================================

        // INITIALIZATION

        // =========================================



        init() {

            const element = this.$el.querySelector(

                '[data-booking-config]'

            );



            if (!element) {

                this.errorMessage =

                    'Konfigurasi booking tidak ditemukan.';

                return;

            }



            try {

                this.config = JSON.parse(element.textContent);



                this.services = this.config.services || [];

                this.factors = this.config.factors || [];



                this.config.mode = this.config.mode || 'staff';



                this.config.urls = this.config.urls || {};



            } catch (error) {

                console.error(

                    'Booking configuration error:',

                    error

                );



                this.errorMessage =

                    'Gagal memuat konfigurasi booking.';



                return;

            }



            // Menggunakan tanggal lokal Indonesia.

            const now = new Date();



            const indonesiaDate = new Intl.DateTimeFormat(

                'en-CA',

                {

                    timeZone: 'Asia/Jakarta',

                    year: 'numeric',

                    month: '2-digit',

                    day: '2-digit',

                }

            ).format(now);



            this.today = indonesiaDate;

            this.bookingDate = indonesiaDate;

        },



        // =========================================

        // DURATION FACTORS

        // =========================================



        factorsByCategory(category) {

            return this.factors.filter(

                factor => factor.kategori === category

            );

        },



        // =========================================

        // CUSTOMER

        // =========================================



        handleCustomerInput(value) {

            this.customerQuery = value;



            if (this.isPublicBooking) {

                this.customerPhone = value;

            }



            this.customerLookupDone = false;

            this.customerLookupLoading = false;

            this.customerResults = [];

            this.searchMessage = '';



            this.lookupRequestId++;

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



                    ...(token

                        ? { 'X-CSRF-TOKEN': token }

                        : {}),



                    ...(options.headers || {}),

                },

            });



            const data = await response.json().catch(() => ({}));



            if (!response.ok) {

                throw new Error(

                    data.message ||

                    Object.values(data.errors || {}).flat()[0] ||

                    'Terjadi kesalahan pada server.'

                );

            }



            return data;

        },



        async searchCustomers() {

            const query = this.customerQuery.trim();



            this.customerResults = [];

            this.searchMessage = '';

            this.customerLookupDone = false;



            if (this.isPublicBooking) {

                this.customerPhone = query;

            }



            const digitCount = query.replace(/\D/g, '').length;



            if (digitCount < 8) {

                if (query.length) {

                    this.searchMessage =

                        'Masukkan nomor WhatsApp minimal 8 digit.';

                }



                return;

            }



            const requestId = ++this.lookupRequestId;



            this.customerLookupLoading = true;



            try {

                const url = new URL(

                    this.config.urls.pelanggan,

                    window.location.origin

                );



                url.searchParams.set('q', query);



                const result = await this.request(

                    url.toString(),

                    {

                        method: 'GET',

                    }

                );



                if (requestId !== this.lookupRequestId) {

                    return;

                }



                this.customerResults = result.data || [];

                this.customerLookupDone = true;



                if (!this.customerResults.length) {

                    this.searchMessage =

                        'Nomor WhatsApp belum terdaftar. Silakan lengkapi data pelanggan baru.';

                }



            } catch (error) {

                if (requestId === this.lookupRequestId) {

                    this.searchMessage = error.message;

                    this.customerLookupDone = false;

                }



            } finally {

                if (requestId === this.lookupRequestId) {

                    this.customerLookupLoading = false;

                }

            }

        },



        selectCustomer(customer) {

            this.selectedCustomer = customer;



            this.customerName = customer.nama || '';

            this.customerPhone = customer.no_wa || '';

            this.customerQuery = customer.no_wa || '';



            this.customerPets = Array.isArray(customer.pets)

                ? customer.pets

                : [];



            this.customerResults = [];

            this.searchMessage = '';



            this.customerLookupDone = true;

            this.selectedPetId = '';



            this.resetPet();

            this.resetEstimation();

        },



        clearCustomer() {

            this.selectedCustomer = null;



            this.customerQuery = '';

            this.customerName = '';

            this.customerPhone = '';



            this.customerResults = [];

            this.customerPets = [];



            this.selectedPetId = '';



            this.searchMessage = '';



            this.customerLookupDone = false;

            this.customerLookupLoading = false;



            this.lookupRequestId++;



            this.resetPet();

            this.resetEstimation();

        },



        // =========================================

        // PET

        // =========================================



        resetPet() {

            this.pet = {

                nama: '',

                spesies: '',

                ras: '',

                ukuran: '',

                jenis_bulu: '',

                perilaku: '',

                alergi: '',

                catatan_kesehatan: '',

            };



            this.conditions = [];

        },



        selectExistingPet() {

            const selected = this.customerPets.find(

                pet => String(pet.id) === String(this.selectedPetId)

            );



            if (!selected) {

                this.selectedPetId = '';



                this.resetPet();

                this.resetEstimation();



                return;

            }



            this.pet = {

                nama: selected.nama || '',

                spesies: selected.spesies || '',

                ras: selected.ras || '',

                ukuran: selected.ukuran || '',

                jenis_bulu: selected.jenis_bulu || '',

                perilaku: selected.perilaku || '',

                alergi: selected.alergi || '',

                catatan_kesehatan:

                    selected.catatan_kesehatan || '',

            };



            this.conditions = [];



            this.resetEstimation();

        },



        petPayload() {

            return {

                spesies: this.pet.spesies,

                ukuran: this.pet.ukuran,

                jenis_bulu: this.pet.jenis_bulu,

                perilaku: this.pet.perilaku,

            };

        },



        // =========================================

        // VALIDATION

        // =========================================



        validateCustomer() {

            if (this.selectedCustomer) {

                return;

            }



            if (

                this.isPublicBooking &&

                !this.customerLookupDone

            ) {

                throw new Error(

                    'Periksa nomor WhatsApp terlebih dahulu.'

                );

            }



            if (this.customerLookupLoading) {

                throw new Error(

                    'Sedang memeriksa data pelanggan. Tunggu sebentar.'

                );

            }



            if (!this.customerName.trim()) {

                throw new Error(

                    'Nama pelanggan wajib diisi.'

                );

            }



            if (!this.customerPhone.trim()) {

                throw new Error(

                    'Nomor WhatsApp wajib diisi.'

                );

            }



            const digitCount =

                this.customerPhone.replace(/\D/g, '').length;



            if (digitCount < 8) {

                throw new Error(

                    'Nomor WhatsApp minimal 8 digit.'

                );

            }

        },



        validatePet() {

            if (this.selectedPetId) {

                if (

                    !this.customerPets.some(

                        pet =>

                            String(pet.id) ===

                            String(this.selectedPetId)

                    )

                ) {

                    throw new Error(

                        'Data hewan terpilih tidak valid. Silakan pilih ulang.'

                    );

                }



                return;

            }



            for (const [field, label] of [

                ['nama', 'Nama hewan'],

                ['spesies', 'Spesies'],

                ['ukuran', 'Ukuran'],

                ['jenis_bulu', 'Jenis bulu'],

                ['perilaku', 'Perilaku'],

            ]) {

                if (!String(this.pet[field] || '').trim()) {

                    throw new Error(

                        `${label} wajib diisi.`

                    );

                }

            }

        },



        validateServices() {

            if (!this.selectedServices.length) {

                throw new Error(

                    'Pilih minimal satu layanan.'

                );

            }

        },



        // =========================================

        // RESET ESTIMATION AND SCHEDULE

        // =========================================



        resetEstimation() {

            this.estimation = null;



            this.slots = [];

            this.weeklySchedule = [];



            this.selectedSlot = null;



            this.slotMessage = '';



            this.slotRequestId++;

        },



        // =========================================

        // CALCULATE DURATION

        // =========================================



        async calculateDuration() {
            this.errorMessage = '';

            try {
                this.validateCustomer();
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

                if (this.isPublicBooking) {
                    await this.loadWeeklySchedule();
                } else if (this.bookingDate) {
                    await this.findSlots();
                }
            } catch (error) {
                this.errorMessage = error.message;
            } finally {
                this.loadingEstimation = false;
            }
        },

        // =========================================

        // =========================================

        // GET NEXT SEVEN DAYS

        // =========================================



        getNextSevenDates() {
            const dates = [];
            const [year, month, day] = this.today.split('-').map(Number);
            const startDate = new Date(Date.UTC(year, month - 1, day));

            for (let offset = 0; offset < 7; offset++) {
                const date = new Date(startDate);
                date.setUTCDate(startDate.getUTCDate() + offset);

                dates.push([
                    date.getUTCFullYear(),
                    String(date.getUTCMonth() + 1).padStart(2, '0'),
                    String(date.getUTCDate()).padStart(2, '0'),
                ].join('-'));
            }

            return dates;
        },

        async loadWeeklySchedule() {
            this.errorMessage = '';
            this.slotMessage = '';
            this.slots = [];
            this.selectedSlot = null;

            let requestId = null;

            try {
                this.validateCustomer();
                this.validatePet();
                this.validateServices();

                if (!this.estimation) {
                    throw new Error('Hitung estimasi durasi terlebih dahulu.');
                }

                this.loadingSlots = true;
                requestId = ++this.slotRequestId;
                const dates = this.getNextSevenDates();

                const results = await Promise.all(
                    dates.map(async date => {
                        try {
                            const result = await this.request(
                                this.config.urls.slot,
                                {
                                    method: 'POST',
                                    body: JSON.stringify({
                                        pet: this.petPayload(),
                                        service_ids: this.selectedServices,
                                        kondisi: this.conditions,
                                        tanggal: date,
                                    }),
                                }
                            );

                            return {
                                date,
                                slots: Array.isArray(result.slots) ? result.slots : [],
                                reason: result.alasan || '',
                                duration: Number(result.durasi_menit || 0),
                            };
                        } catch (error) {
                            return {
                                date,
                                slots: [],
                                reason: error.message || 'Jadwal tidak dapat diperiksa.',
                                duration: 0,
                            };
                        }
                    })
                );

                if (requestId !== this.slotRequestId) return;

                this.weeklySchedule = results;

                const currentDay = results.find(day => day.date === this.bookingDate);
                if (currentDay?.duration) {
                    this.estimation = {
                        ...this.estimation,
                        durasi_menit: currentDay.duration,
                    };
                }

                this.updateSelectedDaySlots();
            } catch (error) {
                this.slotMessage = error.message;
            } finally {
                if (requestId !== null && this.slotRequestId === requestId) {
                    this.loadingSlots = false;
                }
            }
        },

        async findSlots() {
            if (this.isPublicBooking) {
                return this.findSlotsForDate(this.bookingDate);
            }

            this.errorMessage = '';
            this.slotMessage = '';
            this.slots = [];
            this.selectedSlot = null;

            if (!this.bookingDate) return;

            let requestId = null;

            try {
                this.validateCustomer();
                this.validatePet();
                this.validateServices();

                if (!this.estimation) {
                    throw new Error('Hitung estimasi durasi terlebih dahulu.');
                }

                this.loadingSlots = true;
                requestId = ++this.slotRequestId;

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

                if (requestId !== this.slotRequestId) return;

                this.slots = Array.isArray(result.slots) ? result.slots : [];
                this.estimation = {
                    ...this.estimation,
                    durasi_menit: Number(result.durasi_menit || this.estimation.durasi_menit),
                };
                this.slotMessage = result.alasan || (
                    this.slots.length ? '' : 'Belum ada jadwal tersedia pada tanggal ini.'
                );
            } catch (error) {
                this.slotMessage = error.message;
            } finally {
                if (requestId !== null && this.slotRequestId === requestId) {
                    this.loadingSlots = false;
                }
            }
        },

        async findSlotsForDate(date) {
            if (!date) return;

            this.errorMessage = '';
            this.slotMessage = '';
            this.slots = [];
            this.selectedSlot = null;

            if (date < this.today) {
                this.slotMessage = 'Tanggal booking tidak boleh sebelum hari ini.';
                return;
            }

            const cachedDay = this.weeklySchedule.find(day => day.date === date);
            if (cachedDay) {
                this.bookingDate = date;
                this.updateSelectedDaySlots();
                return;
            }

            let requestId = null;

            try {
                this.validateCustomer();
                this.validatePet();
                this.validateServices();

                if (!this.estimation) {
                    throw new Error('Hitung estimasi durasi terlebih dahulu.');
                }

                this.loadingSlots = true;
                requestId = ++this.slotRequestId;

                const result = await this.request(
                    this.config.urls.slot,
                    {
                        method: 'POST',
                        body: JSON.stringify({
                            pet: this.petPayload(),
                            service_ids: this.selectedServices,
                            kondisi: this.conditions,
                            tanggal: date,
                        }),
                    }
                );

                if (requestId !== this.slotRequestId) return;

                this.bookingDate = date;
                this.slots = Array.isArray(result.slots) ? result.slots : [];
                this.slotMessage = result.alasan || (
                    this.slots.length ? '' : 'Belum ada jadwal tersedia pada tanggal ini.'
                );
                this.estimation = {
                    ...this.estimation,
                    durasi_menit: Number(result.durasi_menit || this.estimation.durasi_menit),
                };
            } catch (error) {
                this.bookingDate = date;
                this.slotMessage = error.message;
            } finally {
                if (requestId !== null && this.slotRequestId === requestId) {
                    this.loadingSlots = false;
                }
            }
        },

        // =========================================

        // =========================================

        // WEEKLY CALENDAR FORMATTING

        // =========================================



        formatWeekDay(value) {
            return this.dateFromIso(value).toLocaleDateString('id-ID', {
                weekday: 'short',
                timeZone: 'UTC',
            });
        },

        formatDayNumber(value) {
            return Number(value.split('-')[2]);
        },

        formatMonth(value) {
            return this.dateFromIso(value).toLocaleDateString('id-ID', {
                month: 'short',
                timeZone: 'UTC',
            });
        },

        formatLongDate(value) {
            if (!value) return '-';

            return this.dateFromIso(value).toLocaleDateString('id-ID', {
                weekday: 'long',
                day: 'numeric',
                month: 'long',
                year: 'numeric',
                timeZone: 'UTC',
            });
        },

        dateFromIso(value) {
            const [year, month, day] = value.split('-').map(Number);
            return new Date(Date.UTC(year, month - 1, day, 12));
        },

        isRecommendedDay(date) {
            return this.recommendedDays.includes(date);
        },

        async selectBookingDate(date) {
            if (!date) return;

            this.bookingDate = date;
            this.selectedSlot = null;
            this.errorMessage = '';

            if (!this.estimation) {
                this.slots = [];
                this.slotMessage = 'Hitung estimasi durasi terlebih dahulu untuk melihat jadwal.';
                return;
            }

            await this.findSlotsForDate(date);
        },

        updateSelectedDaySlots() {
            const selectedDay = this.weeklySchedule.find(
                day => day.date === this.bookingDate
            );

            this.slots = selectedDay?.slots || [];
            this.slotMessage = this.slots.length
                ? ''
                : selectedDay?.reason || 'Belum ada jadwal tersedia pada tanggal ini.';
        },

        // =========================================

        // =========================================

        // SELECT SLOT

        // =========================================



        selectSlot(slot) {

            this.selectedSlot = slot;



            this.errorMessage = '';

        },



        // =========================================

        // TIME FORMATTING

        // =========================================



        formatTime(value) {

            if (!value) {

                return '-';

            }



            const match = String(value).match(/(?:^| )([01]\d|2[0-3]):([0-5]\d)/);
            return match ? `${match[1]}:${match[2]}` : '-';

        },



        formatCurrency(value) {

            return new Intl.NumberFormat('id-ID', {

                style: 'currency',

                currency: 'IDR',

                maximumFractionDigits: 0,

            }).format(Number(value || 0));

        },



        // =========================================

        // SUBMIT BOOKING

        // =========================================




async submitBooking() {
    this.errorMessage = '';

    try {
        this.validateCustomer();
        this.validatePet();
        this.validateServices();

        if (!this.selectedSlot) {
            throw new Error(
                'Pilih slot booking terlebih dahulu.'
            );
        }

        if (!this.estimation) {
            throw new Error(
                'Hitung estimasi durasi sebelum konfirmasi booking.'
            );
        }

        this.submitting = true;

        // Data dasar booking.
        const payload = {
            no_wa: this.selectedCustomer
                ? this.selectedCustomer.no_wa
                : this.customerPhone,

            nama_pelanggan: this.selectedCustomer
                ? null
                : this.customerName,

            customer_id: this.selectedCustomer
                ? Number(this.selectedCustomer.id)
                : null,

            service_ids: this.selectedServices.map(Number),

            kondisi: this.conditions,

            tanggal: this.bookingDate,

            mulai: this.selectedSlot.mulai,

            catatan_pelanggan: this.customerNote,

            idempotency_key: this.createIdempotencyKey(),
        };

        // PENTING:
        // Hewan lama hanya mengirim pet_id.
        // Jangan mengirim pet: null.
        if (this.selectedPetId) {
            payload.pet_id = Number(this.selectedPetId);
        } else {
            // Hewan baru mengirim object pet.
            payload.pet = {
                nama: this.pet.nama,
                spesies: this.pet.spesies,
                ras: this.pet.ras || null,
                ukuran: this.pet.ukuran,
                jenis_bulu: this.pet.jenis_bulu,
                perilaku: this.pet.perilaku,
                alergi: this.pet.alergi || null,
                catatan_kesehatan:
                    this.pet.catatan_kesehatan || null,
            };
        }

        const result = await this.request(
            this.config.urls.konfirmasi,
            {
                method: 'POST',
                body: JSON.stringify(payload),
            }
        );

        this.bookingResult = result.booking;

        // Jangan langsung berpindah ke halaman status.
        // Tampilkan ringkasan sukses pada Blade agar pelanggan dapat
        // memeriksa detail dan menyalin tautan status terlebih dahulu.

    } catch (error) {
        this.errorMessage = error.message;

    } finally {
        this.submitting = false;
    }
},



        async copyStatusLink() {
            const url = this.bookingResult?.status_url;

            if (!url) {
                this.errorMessage = 'Tautan status booking belum tersedia.';
                return;
            }

            try {
                if (navigator.clipboard?.writeText) {
                    await navigator.clipboard.writeText(url);
                } else {
                    const input = document.createElement('textarea');
                    input.value = url;
                    input.setAttribute('readonly', '');
                    input.style.position = 'absolute';
                    input.style.left = '-9999px';
                    document.body.appendChild(input);
                    input.select();
                    const copied = document.execCommand('copy');
                    input.remove();
                    if (!copied) throw new Error('Salin tautan secara manual.');
                }

                this.statusLinkCopied = true;
            } catch (error) {
                this.errorMessage = 'Tautan tidak dapat disalin otomatis. Silakan salin dari tombol Lihat Status Booking.';
            }
        },

        // =========================================

        // IDEMPOTENCY

        // =========================================



        createIdempotencyKey() {

            if (window.crypto?.randomUUID) {

                return window.crypto

                    .randomUUID()

                    .replaceAll('-', '');

            }



            return (

                Date.now().toString(36) +

                Math.random().toString(36).slice(2) +

                Math.random().toString(36).slice(2)

            ).slice(0, 64);

        },

    };

}
