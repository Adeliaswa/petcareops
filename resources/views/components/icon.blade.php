@props(['name', 'size' => ''])
<svg class="icon {{ $size ? 'icon--'.$size : '' }}" viewBox="0 0 24 24" aria-hidden="true" focusable="false">
    @switch($name)
        @case('home') <path d="m3 10 9-7 9 7v10a1 1 0 0 1-1 1h-5v-7H9v7H4a1 1 0 0 1-1-1z"/> @break
        @case('calendar') <rect x="3" y="5" width="18" height="16" rx="2"/><path d="M16 3v4M8 3v4M3 10h18M8 14h.01M12 14h.01M16 14h.01M8 17h.01M12 17h.01"/> @break
        @case('users') <path d="M16 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2M10 11a4 4 0 1 0 0-8 4 4 0 0 0 0 8ZM20 21v-2a4 4 0 0 0-3-3.87M16 3.13a4 4 0 0 1 0 7.75"/> @break
        @case('paw') <path d="M12 13c-2.5 0-5 2.1-5 4.5A3.5 3.5 0 0 0 10.5 21h3a3.5 3.5 0 0 0 3.5-3.5c0-2.4-2.5-4.5-5-4.5ZM5.5 12a2 2 0 1 0 0-4 2 2 0 0 0 0 4ZM10 7a2 2 0 1 0 0-4 2 2 0 0 0 0 4ZM18.5 12a2 2 0 1 0 0-4 2 2 0 0 0 0 4ZM14 7a2 2 0 1 0 0-4 2 2 0 0 0 0 4Z"/> @break
        @case('tag') <path d="M20.6 13.4 11 3.8H4v7l9.6 9.6a2 2 0 0 0 2.8 0l4.2-4.2a2 2 0 0 0 0-2.8Z"/><circle cx="7.5" cy="7.5" r=".8"/> @break
        @case('sliders') <path d="M4 21v-7m0-4V3m8 18v-9m0-4V3m8 18v-5m0-4V3M2 14h4m4-6h4m4 8h4"/> @break
        @case('scissors') <circle cx="6" cy="6" r="3"/><circle cx="6" cy="18" r="3"/><path d="m8.1 8.1 12.8 12.8M8.1 15.9 20 4"/> @break
        @case('sun') <circle cx="12" cy="12" r="4"/><path d="M12 2v2m0 16v2M4.93 4.93l1.42 1.42m11.3 11.3 1.42 1.42M2 12h2m16 0h2M4.93 19.07l1.42-1.42m11.3-11.3 1.42-1.42"/> @break
        @case('user') <circle cx="12" cy="8" r="4"/><path d="M4 21a8 8 0 0 1 16 0"/> @break
        @case('user-cog') <circle cx="9" cy="8" r="4"/><path d="M2 21a7 7 0 0 1 13.5-2.5M19 14l.6 1.2 1.3.2-.9 1 .2 1.3-1.2-.6-1.2.6.2-1.3-.9-1 1.3-.2z"/> @break
        @case('logout') <path d="M10 17l5-5-5-5M15 12H3M12 3h6a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2h-6"/> @break
        @case('chevron-down') <path d="m6 9 6 6 6-6"/> @break
        @case('plus') <path d="M12 5v14M5 12h14"/> @break
        @case('clock') <circle cx="12" cy="12" r="9"/><path d="M12 7v5l3 2"/> @break
        @case('check-circle') <circle cx="12" cy="12" r="9"/><path d="m8 12 2.5 2.5L16 9"/> @break
        @case('arrow-up-right') <path d="M7 17 17 7M7 7h10v10"/> @break
        @case('mail') <rect x="3" y="5" width="18" height="14" rx="2"/><path d="m3 7 9 6 9-6"/> @break
        @default <circle cx="12" cy="12" r="9"/>
    @endswitch
</svg>
