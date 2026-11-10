
export default function masterCrud(config) {
    return {
        config,
        rows: [],
        search: '',
        loading: false,
        saving: false,

        modalOpen: false,
        editing: null,
        form: {},
        errors: {},
        formError: '',

        notice: '',
        noticeType: 'success',

        pagination: {
            current_page: 1,
            last_page: 1,
            total: 0,
        },

        async init() {
            await this.load();

            if (this.config.kind === 'pet') {
                await this.loadPetOptions();
            }

            if (
                this.config.kind === 'shift' ||
                this.config.kind === 'timeoff'
            ) {
                await this.load();
            }
        },

        headers() {
            return {
                'Accept': 'application/json',
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN':
                    document.querySelector('meta[name="csrf-token"]')?.content || '',
            };
        },

        async request(url, options = {}) {
            const res = await fetch(url, {
                ...options,
                headers: {
                    ...this.headers(),
                    ...(options.headers || {}),
                },
            });

            let body = {};

            try {
                body = await res.json();
            } catch {
                body = {};
            }

            if (!res.ok) {
                const error = new Error(
                    body.message || 'Permintaan gagal.'
                );

                error.status = res.status;
                error.body = body;

                throw error;
            }

            return body;
        },

        async load(page = 1) {
            this.loading = true;

            try {
                let url = this.endpoint();

                const params = new URLSearchParams();

                if (
                    this.search &&
                    ['customer', 'pet'].includes(this.config.kind)
                ) {
                    params.set('search', this.search);
                }

                if (page > 1) {
                    params.set('page', page);
                }

                if (params.toString()) {
                    url += (url.includes('?') ? '&' : '?') + params;
                }

                const body = await this.request(url);

                const payload = Array.isArray(body.data)
                    ? body
                    : (body.data || body);

                this.rows = Array.isArray(payload)
                    ? payload
                    : (payload.data || []);

                this.pagination = {
                    current_page: payload.current_page || 1,
                    last_page: payload.last_page || 1,
                    total: payload.total ?? this.rows.length,
                };

            } catch (error) {
                this.flash(error.message, 'error');

            } finally {
                this.loading = false;
            }
        },

        endpoint() {
            if (
                this.config.kind === 'alltimeoff' ||
                this.config.kind === 'shift' ||
                this.config.kind === 'timeoff'
            ) {
                return this.config.api;
            }

            return this.config.api;
        },

        /**
         * Memuat pilihan pemilik dan faktor durasi.
         * Kedua API diproses secara independen agar
         * kegagalan salah satu API tidak menggagalkan lainnya.
         */
        async loadPetOptions() {
            const customerField = this.config.fields.find(
                field => field.name === 'customer_id'
            );

            const factorFields = this.config.fields.filter(
                field => [
                    'ukuran',
                    'jenis_bulu',
                    'perilaku',
                ].includes(field.name)
            );

            const results = await Promise.allSettled([
                this.request('/staf/pemilik'),
                this.request('/staf/faktor-durasi/options'),
            ]);

            // Pilihan pemilik
            if (results[0].status === 'fulfilled') {
                const customerRows = results[0].value.data || [];

                if (customerField) {
                    customerField.options = customerRows.map(customer => ({
                        value: customer.id,
                        label: customer.nama,
                    }));
                }

            } else {
                console.error(
                    'Gagal memuat pilihan pemilik:',
                    results[0].reason
                );

                if (customerField) {
                    customerField.options = [];
                }

                this.flash(
                    'Data pemilik gagal dimuat: ' +
                    results[0].reason.message,
                    'error'
                );
            }

            // Pilihan faktor durasi
            if (results[1].status === 'fulfilled') {
                const factorRows = results[1].value.data || [];

                factorFields.forEach(field => {
                    field.options = factorRows
                        .filter(factor => factor.kategori === field.name)
                        .map(factor => ({
                            value: factor.key,
                            label: factor.label,
                        }));
                });

            } else {
                console.error(
                    'Gagal memuat faktor durasi:',
                    results[1].reason
                );

                factorFields.forEach(field => {
                    field.options = [];
                });

                this.flash(
                    'Pilihan ukuran, jenis bulu, dan perilaku gagal dimuat: ' +
                    results[1].reason.message,
                    'error'
                );
            }
        },

        optionsFor(field) {
            return field.options || [];
        },

        value(row, col) {
            return col.key.split('.').reduce(
                (value, key) => value?.[key],
                row
            );
        },

        format(value, col) {
            if (
                value === null ||
                value === undefined ||
                value === ''
            ) {
                return '—';
            }

            if (col.type === 'currency') {
                return new Intl.NumberFormat('id-ID', {
                    style: 'currency',
                    currency: 'IDR',
                    maximumFractionDigits: 0,
                }).format(value);
            }

            if (col.type === 'number') {
                return new Intl.NumberFormat('id-ID').format(value);
            }

            if (col.type === 'date') {
                return new Date(value + 'T00:00:00')
                    .toLocaleDateString('id-ID');
            }

            if (col.type === 'day') {
                return [
                    '',
                    'Senin',
                    'Selasa',
                    'Rabu',
                    'Kamis',
                    'Jumat',
                    'Sabtu',
                    'Minggu',
                ][Number(value)] || value;
            }

            if (
                typeof value === 'boolean' ||
                col.type === 'badge'
            ) {
                return (
                    value === true ||
                    value === 1 ||
                    value === '1'
                )
                    ? 'Aktif'
                    : (
                        value === false ||
                        value === 0 ||
                        value === '0'
                    )
                        ? 'Nonaktif'
                        : value;
            }

            return value;
        },

        badgeClass(value) {
            return (
                value === true ||
                value === 1 ||
                value === '1'
            )
                ? 'is-active'
                : 'is-inactive';
        },

        blankForm() {
            const object = {};

            this.config.fields.forEach(field => {
                object[field.name] =
                    field.type === 'checkbox' ? true : '';
            });

            return object;
        },

        openCreate() {
            this.editing = null;
            this.form = this.blankForm();
            this.errors = {};
            this.formError = '';
            this.modalOpen = true;
        },

        openEdit(row) {
            this.editing = row;
            this.form = this.blankForm();

            this.config.fields.forEach(field => {
                this.form[field.name] =
                    row[field.name] ??
                    (field.type === 'checkbox' ? false : '');
            });

            this.errors = {};
            this.formError = '';
            this.modalOpen = true;
        },

        closeModal() {
            this.modalOpen = false;
        },

        async save() {
            this.saving = true;
            this.errors = {};
            this.formError = '';

            try {
                const url = this.editing
                    ? this.itemUrl(this.editing)
                    : this.endpoint();

                const method = this.editing ? 'PUT' : 'POST';

                const body = await this.request(url, {
                    method,
                    body: JSON.stringify(this.form),
                });

                this.flash(
                    body.message || 'Data berhasil disimpan.'
                );

                this.closeModal();

                await this.load(this.pagination.current_page);

            } catch (error) {
                if (
                    error.status === 422 &&
                    error.body.errors
                ) {
                    Object.entries(error.body.errors).forEach(
                        ([key, messages]) => {
                            this.errors[key] = messages[0];
                        }
                    );

                    this.formError =
                        'Periksa kembali isian yang ditandai.';

                } else {
                    this.formError = error.message;
                }

            } finally {
                this.saving = false;
            }
        },

        itemUrl(row) {
            return this.endpoint().replace(/\/$/, '') + '/' + row.id;
        },

        async remove(row) {
            if (
                !confirm(
                    `Hapus data ${row.nama || row.label || row.id}?`
                )
            ) {
                return;
            }

            try {
                const body = await this.request(
                    this.itemUrl(row),
                    { method: 'DELETE' }
                );

                this.flash(
                    body.message || 'Data berhasil dihapus.'
                );

                await this.load();

            } catch (error) {
                this.flash(error.message, 'error');
            }
        },

        flash(message, type = 'success') {
            this.notice = message;
            this.noticeType = type;

            window.clearTimeout(this._noticeTimer);

            this._noticeTimer = window.setTimeout(() => {
                this.notice = '';
            }, 4500);
        },
    };
}
