@php($menuUser = auth()->user())
<div class="user-menu" x-data="userMenu" @click.outside="close()" @keydown.escape.window="close()">
    <button type="button" class="user-menu__trigger" @click="toggle()" :aria-expanded="open.toString()" aria-haspopup="true">
        <span class="user-menu__avatar">{{ mb_strtoupper(mb_substr($menuUser->name, 0, 1)) }}</span>
        <span class="user-menu__who">{{ $menuUser->name }}</span>
        <x-icon name="chevron-down" />
    </button>
    <div class="user-menu__panel" x-cloak x-show="open" x-transition>
        <div class="user-menu__head">
            <span class="user-menu__name">{{ $menuUser->name }}</span>
            <span class="user-menu__email">{{ $menuUser->email }}</span>
        </div>
        <a class="user-menu__item" href="{{ route('profile.edit') }}"><x-icon name="user" /> Profil saya</a>
    </div>
</div>
