<section class="master-page"
         x-data="masterCrud(@js($masterConfig))"
         x-init="init()">

    {{-- Heading --}}
    <div class="master-heading">
        <div>
            <p class="master-eyebrow">PET CARE OPS / DATA MASTER</p>
            <h1>{{ $masterConfig['title'] }}</h1>
            <p>{{ $masterConfig['description'] }}</p>
        </div>

        @if($masterConfig['canCreate'] ?? true)
            <button type="button"
                    class="btn btn--primary"
                    @click="openCreate()">
                <x-icon name="plus" />
                Tambah {{ strtolower($masterConfig['singular']) }}
            </button>
        @endif
    </div>

    {{-- Search --}}
    <div class="master-toolbar">
        <label class="master-search">
            <x-icon name="search" />

            <input
                type="search"
                x-model="search"
                @input.debounce.350ms="load(1)"
                placeholder="Cari {{ strtolower($masterConfig['singular']) }}..."
            >
        </label>

        <span class="master-count"
              x-text="`${pagination.total ?? rows.length} data`">
        </span>
    </div>

    {{-- Notifikasi --}}
    <template x-if="notice">
        <div class="master-notice"
             :class="noticeType"
             role="status"
             x-text="notice">
        </div>
    </template>

    {{-- Tabel --}}
    <div class="master-table-wrap">
        <table class="master-table">

            <thead>
                <tr>
                    <template x-for="col in config.columns" :key="col.key">
                        <th x-text="col.label"></th>
                    </template>

                    <th class="master-action-head">Aksi</th>
                </tr>
            </thead>

            <tbody>

                {{-- Loading --}}
                <template x-if="loading">
                    <tr>
                        <td :colspan="config.columns.length + 1"
                            class="master-state">
                            Memuat data...
                        </td>
                    </tr>
                </template>

                {{-- Kosong --}}
                <template x-if="!loading && rows.length === 0">
                    <tr>
                        <td :colspan="config.columns.length + 1"
                            class="master-state">
                            <strong>Belum ada data</strong>
                            <span>Data akan muncul setelah ditambahkan.</span>
                        </td>
                    </tr>
                </template>

                {{-- Data --}}
                <template x-for="row in rows" :key="row.id">
                    <tr>

                        <template x-for="col in config.columns" :key="col.key">
                            <td>
                                <template x-if="col.type === 'badge'">
                                    <span class="master-badge"
                                          :class="badgeClass(value(row, col))"
                                          x-text="format(value(row, col), col)">
                                    </span>
                                </template>

                                <template x-if="col.type !== 'badge'">
                                    <span x-text="format(value(row, col), col)"></span>
                                </template>
                            </td>
                        </template>

                        {{-- Aksi --}}
                        <td class="master-actions">

                            {{-- Edit --}}
                            <button
                                type="button"
                                class="master-icon-btn"
                                title="Ubah"
                                aria-label="Ubah data"
                                @click="openEdit(row)"
                            >
                                <x-icon name="pencil" />
                            </button>

                            {{-- Hapus --}}
                            <button
                                type="button"
                                class="master-icon-btn master-icon-btn--danger"
                                title="Hapus"
                                aria-label="Hapus data"
                                x-show="config.canDelete"
                                @click="remove(row)"
                            >
                                <x-icon name="trash-2" />
                            </button>

                            {{-- Jadwal Shift Groomer --}}
                            <template x-if="config.detailBase">
                                <a
                                    class="master-icon-btn"
                                    :href="config.detailBase + row.id + '/shift'"
                                    title="Jadwal Shift"
                                    aria-label="Kelola jadwal shift groomer"
                                >
                                    <x-icon name="calendar-days" />
                                </a>
                            </template>

                            {{-- Jadwal Libur Groomer --}}
                            <template x-if="config.detailTimeOffBase">
                                <a
                                    class="master-icon-btn"
                                    :href="config.detailTimeOffBase + row.id + '/libur'"
                                    title="Jadwal Libur"
                                    aria-label="Kelola jadwal libur groomer"
                                >
                                    <x-icon name="sun" />
                                </a>
                            </template>

                        </td>
                    </tr>
                </template>

            </tbody>
        </table>
    </div>

    {{-- Pagination --}}
    <div class="master-pagination"
         x-show="pagination.last_page > 1">

        <button
            type="button"
            class="btn btn--secondary"
            :disabled="pagination.current_page <= 1"
            @click="load(pagination.current_page - 1)">
            Sebelumnya
        </button>

        <span x-text="`Halaman ${pagination.current_page} dari ${pagination.last_page}`"></span>

        <button
            type="button"
            class="btn btn--secondary"
            :disabled="pagination.current_page >= pagination.last_page"
            @click="load(pagination.current_page + 1)">
            Berikutnya
        </button>
    </div>

    {{-- Modal --}}
    <div class="master-modal-backdrop"
         x-show="modalOpen"
         x-cloak
         @keydown.escape.window="closeModal()"
         @click.self="closeModal()">

        <section class="master-modal"
                 role="dialog"
                 aria-modal="true"
                 :aria-label="editing ? 'Ubah data' : 'Tambah data'">

            <header>
                <div>
                    <p class="master-eyebrow">FORM DATA MASTER</p>

                    <h2 x-text="editing
                        ? `Ubah ${config.singular}`
                        : `Tambah ${config.singular}`">
                    </h2>
                </div>

                <button
                    type="button"
                    class="master-close"
                    @click="closeModal()"
                    aria-label="Tutup">
                    ×
                </button>
            </header>

            <form @submit.prevent="save()">

                <div class="master-form-grid">

                    <template x-for="field in config.fields"
                              :key="field.name">

                        <label class="master-field"
                               :class="{'master-field--wide': field.wide}">

                            <span x-text="field.label"></span>

                            {{-- Select --}}
                            <template x-if="field.type === 'select'">
                                <select
                                    x-model="form[field.name]"
                                    :required="!!field.required">

                                    <option value="">Pilih</option>

                                    <template x-for="opt in optionsFor(field)"
                                              :key="opt.value">

                                        <option
                                            :value="opt.value"
                                            x-text="opt.label">
                                        </option>

                                    </template>
                                </select>
                            </template>

                            {{-- Textarea --}}
                            <template x-if="field.type === 'textarea'">
                                <textarea
                                    rows="3"
                                    x-model="form[field.name]"
                                    :required="!!field.required"
                                    :maxlength="field.maxlength || null">
                                </textarea>
                            </template>

                            {{-- Checkbox --}}
                            <template x-if="field.type === 'checkbox'">
                                <input
                                    type="checkbox"
                                    x-model="form[field.name]">
                            </template>

                            {{-- Input biasa --}}
                            <template x-if="!['select', 'textarea', 'checkbox'].includes(field.type)">
                                <input
                                    :type="field.type || 'text'"
                                    x-model="form[field.name]"
                                    :required="!!field.required"
                                    :min="field.min ?? null"
                                    :max="field.max ?? null"
                                    :step="field.step ?? null"
                                    :maxlength="field.maxlength ?? null"
                                    :placeholder="field.placeholder || ''">
                            </template>

                            {{-- Error field --}}
                            <small
                                x-show="errors[field.name]"
                                x-text="errors[field.name]">
                            </small>

                        </label>
                    </template>

                </div>

                <p class="master-form-error"
                   x-show="formError"
                   x-text="formError">
                </p>

                <footer>
                    <button
                        type="button"
                        class="btn btn--secondary"
                        @click="closeModal()">
                        Batal
                    </button>

                    <button
                        type="submit"
                        class="btn btn--primary"
                        :disabled="saving"
                        x-text="saving ? 'Menyimpan...' : 'Simpan'">
                    </button>
                </footer>

            </form>
        </section>
    </div>

</section>