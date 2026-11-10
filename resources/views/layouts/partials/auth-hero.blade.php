{{-- Ilustrasi anjing khusus halaman login. Tidak menggunakan gambar logo. --}}
<svg class="auth-svg-defs" aria-hidden="true" focusable="false">
    <symbol id="paw" viewBox="0 0 64 64">
        <ellipse cx="14" cy="28" rx="7" ry="9" transform="rotate(-20 14 28)"/>
        <ellipse cx="26" cy="15" rx="7" ry="10" transform="rotate(-8 26 15)"/>
        <ellipse cx="40" cy="15" rx="7" ry="10" transform="rotate(8 40 15)"/>
        <ellipse cx="52" cy="28" rx="7" ry="9" transform="rotate(20 52 28)"/>
        <path d="M32 30c-10 0-18 9-18 17 0 6 5 9 10 8 4-1 5-2 8-2s4 1 8 2c5 1 10-2 10-8 0-8-8-17-18-17z"/>
    </symbol>
    <symbol id="bone" viewBox="0 0 64 24">
        <circle cx="10" cy="7" r="6"/><circle cx="10" cy="17" r="6"/>
        <circle cx="54" cy="7" r="6"/><circle cx="54" cy="17" r="6"/>
        <rect x="10" y="7" width="44" height="10" rx="4"/>
    </symbol>
</svg>

<aside class="auth-hero" aria-label="Ilustrasi Pet Care Ops">
    <span class="auth-blob" aria-hidden="true"></span>

    <svg class="auth-bone auth-bone--one" aria-hidden="true"><use href="#bone"/></svg>
    <svg class="auth-bone auth-bone--two" aria-hidden="true"><use href="#bone"/></svg>
    <svg class="auth-bone auth-bone--three" aria-hidden="true"><use href="#bone"/></svg>

    <svg class="auth-paw auth-paw--hero-one" aria-hidden="true"><use href="#paw"/></svg>
    <svg class="auth-paw auth-paw--hero-two" aria-hidden="true"><use href="#paw"/></svg>

    <div class="auth-stage">
        <p class="auth-bubble" aria-hidden="true">Wuf! Wuf!</p>

        {{-- SVG maskot anjing dari referensi login; fill ditulis eksplisit agar tidak berubah hitam --}}
        <svg class="auth-dog" viewBox="79 2 134 101" xmlns="http://www.w3.org/2000/svg"
             role="img" aria-label="Ilustrasi anjing lucu Pet Care Ops"
             preserveAspectRatio="xMidYMid meet">
            <g class="auth-dog__motion">
                <path fill="#FFFFFF" stroke="none"
                      d="M125.239 15C152.169 2.43654 165.093 3.46731 202.239 39.5C210.212 47.9892 212.437 75.5257 184.239 69.4119C168.705 77.3948 155.233 80.7189 144.065 81.1321C137.611 81.3709 131.927 80.6375 127.059 79.2694C116.792 76.3838 110.156 70.6743 107.591 65.3054C107.625 64.5087 107.663 63.7499 107.708 63.024C79.7984 50.7983 85.8507 28.6884 125.239 15Z"/>
                <path fill="#FFFFFF" stroke="#533E36" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"
                      d="M127.059 79.2694V84.4692C146.971 88.717 157.158 85.7205 176.86 80.8189L174.596 74.7344C162.455 79.3033 153.803 81.3232 144.065 81.1321C137.611 81.3709 131.927 80.6375 127.059 79.2694Z"/>
                <path fill="none" stroke="#533E36" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"
                      d="M125.239 15C152.169 2.43654 165.093 3.46731 202.239 39.5C210.212 47.9892 212.437 75.5257 184.239 69.4119M127.059 79.2694C116.792 76.3838 110.156 70.6743 107.591 65.3054C107.625 64.5087 107.663 63.7499 107.708 63.024C108.126 56.3866 109.195 52.498 113.477 47.5105C114.679 31.4174 113.48 27.4494 125.239 15M125.239 15C85.8507 28.6884 79.7984 50.7983 107.708 63.024M184.239 69.4119C173.412 61.6959 171.408 45.2641 173.691 24.2402C173.09 44.8197 170.797 56.2671 184.239 69.4119ZM184.239 69.4119C168.705 77.3948 155.233 80.7189 144.065 81.1321M144.065 81.1321C137.611 81.3709 131.927 80.6375 127.059 79.2694M144.065 81.1321C153.803 81.3232 162.455 79.3033 174.596 74.7344L176.86 80.8189C157.158 85.7205 146.971 88.717 127.059 84.4692V79.2694"/>
                <ellipse fill="#FFC5BE" cx="140.44" cy="61.3507" rx="4.83878" ry="3.40226" transform="rotate(89.6671 140.44 61.3507)"/>
                <ellipse fill="#FFC5BE" cx="149.739" cy="83" rx="5.5" ry="3.5"/>
                <ellipse fill="#FFFFFF" stroke="#533E36" stroke-width="2.5" cx="149.739" cy="83" rx="5.5" ry="3.5"/>
                <ellipse class="auth-dog__eye" fill="#533E36" cx="125.239" cy="48.5" rx="4.5" ry="5"/>
                <ellipse class="auth-dog__eye" fill="#533E36" cx="162.239" cy="49.5" rx="4.5" ry="5"/>
                <ellipse fill="#FFC5BE" cx="163.739" cy="63.5" rx="6" ry="4"/>
                <ellipse fill="#FFC5BE" cx="120.239" cy="61" rx="5.5" ry="3.5"/>
                <ellipse fill="#533E36" cx="140.739" cy="52.5" rx="4" ry="2"/>
                <path fill="none" stroke="#533E36" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"
                      d="M131.739 55.5C134.402 61.2064 136.276 60.4467 139.967 55.5C143.167 60.0727 144.878 60.3106 147.739 55.5"/>
                <rect fill="#533E36" x="96" y="84" width="108" height="17" rx="8"/>
                <ellipse fill="#FFFFFF" stroke="#533E36" stroke-width="2.5" cx="124" cy="84" rx="8.5" ry="5.5"/>
                <ellipse fill="#FFFFFF" stroke="#533E36" stroke-width="2.5" cx="176" cy="84" rx="8.5" ry="5.5"/>
                <path fill="none" stroke="#533E36" stroke-width="2" d="M121 84.5v3.5M127 84.5v3.5M173 84.5v3.5M179 84.5v3.5"/>
                <text x="150" y="96.5" text-anchor="middle" fill="#FFC5BE"
                      style="font-family:Fredoka,sans-serif;font-weight:600;font-size:7.5px;letter-spacing:.4px">Selamat datang</text>
            </g>
        </svg>
    </div>

    <div class="auth-brand">
        <p class="auth-wordmark">Pet Care Ops</p>
        <p class="auth-tagline">Pet Grooming &amp; Spa</p>
    </div>
</aside>
