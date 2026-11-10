<x-app-layout>
    <x-slot name="title">Akun Staf</x-slot>


    <header class="page-intro-row">
        <div><p class="eyebrow">PENGATURAN TIM</p><h2 class="content-title">Kelola akun staf</h2><p class="content-lead">Buat akun, atur status akses, dan reset password anggota tim Pet Care Ops.</p></div>
        <a href="{{ route('admin.staff.create') }}" class="btn"><x-icon name="plus" /> Tambah akun staf</a>
    </header>


    @if (session('temporary_password'))
        <section class="temp-password-box" x-data="copyText">
            <p class="temp-password-box__label">Password sementara untuk {{ session('reset_user_name') }}</p>
            <div class="temp-password-box__row">
                <code class="temp-password-box__value">{{ session('temporary_password') }}</code>
                <button type="button" class="temp-password-box__copy" @click="copy(@js(session('temporary_password')))" x-text="label">Salin</button>
            </div>
            <p class="temp-password-box__hint">Salin dan sampaikan password ini kepada pemilik akun melalui kanal yang aman.</p>
        </section>
    @endif


    @if ($errors->has('account'))<div class="form-alert" role="alert">{{ $errors->first('account') }}</div>@endif


    <section class="card account-list-card">
        <div class="section-heading">
            <div><h2 class="section-heading__title">Daftar akun</h2><p class="section-heading__desc">{{ $users->total() }} akun terdaftar.</p></div>
        </div>
        @if ($users->isEmpty())
            <div class="empty-state"><img src="{{ asset('images/cat.svg') }}" alt="" aria-hidden="true"><h3>Belum ada akun staf</h3><p>Tambahkan akun resepsionis atau groomer untuk mulai mengatur akses.</p><a href="{{ route('admin.staff.create') }}" class="btn">Tambah akun pertama</a></div>
        @else
            <div class="table-scroll">
                <table class="data-table">
                    <thead><tr><th>Nama staf</th><th>Peran</th><th>Status</th><th class="text-right">Tindakan</th></tr></thead>
                    <tbody>
                    @foreach ($users as $staffUser)
                        @php($role = $staffUser->getRoleNames()->first() ?? 'staf')
                        <tr>
                            <td><strong>{{ $staffUser->name }}</strong><small>{{ $staffUser->email }}</small></td>
                            <td><x-role-badge :role="$role" /></td>
                            <td><span class="status-pill {{ $staffUser->is_active ? 'status-pill--active' : 'status-pill--inactive' }}">{{ $staffUser->is_active ? 'Aktif' : 'Nonaktif' }}</span></td>
                            <td><div class="table-actions">
                                <form method="POST" action="{{ route('admin.staff.reset-password', $staffUser) }}" onsubmit="return confirm('Reset password akun {{ addslashes($staffUser->name) }}?')">@csrf @method('PUT')<button class="btn btn--small btn--soft" type="submit">Reset password</button></form>
                                @if (auth()->id() !== $staffUser->id)
                                    @if ($staffUser->is_active)
                                        <form method="POST" action="{{ route('admin.staff.deactivate', $staffUser) }}" onsubmit="return confirm('Nonaktifkan akun {{ addslashes($staffUser->name) }}?')">@csrf @method('PATCH')<button class="btn btn--small btn--danger-outline" type="submit">Nonaktifkan</button></form>
                                    @else
                                        <form method="POST" action="{{ route('admin.staff.activate', $staffUser) }}" onsubmit="return confirm('Aktifkan kembali akun {{ addslashes($staffUser->name) }}?')">@csrf @method('PATCH')<button class="btn btn--small btn--soft" type="submit">Aktifkan</button></form>
                                    @endif
                                @endif
                            </div></td>
                        </tr>
                    @endforeach
                    </tbody>
                </table>
            </div>
            <div class="pagination-wrap">{{ $users->links() }}</div>
        @endif
    </section>
</x-app-layout>