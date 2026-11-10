/* Interaksi halaman login PetCareOps.
 * Validasi sisi klien hanya untuk isian kosong; validasi utama tetap dilakukan Laravel.
 */
(() => {
    'use strict';

    const form = document.getElementById('login-form');
    if (!form) return;

    const email = document.getElementById('email');
    const password = document.getElementById('password');
    const toggle = document.getElementById('password-toggle');
    const submit = document.getElementById('login-submit');

    if (!email || !password || !submit) {
        console.warn('[PetCareOps] Elemen form login tidak lengkap. Periksa ID login-form, email, password, dan login-submit.');
        return;
    }

    const defaultLabel = submit.textContent.trim();

    function setError(input, invalid) {
        if (!input) return;

        const field = input.closest('.auth-field');
        const message = field?.querySelector('.auth-error');

        field?.classList.toggle('has-error', invalid);
        input.setAttribute('aria-invalid', invalid ? 'true' : 'false');

        if (invalid && message?.dataset.msg) {
            message.textContent = message.dataset.msg;
        }
    }

    function resetSubmit() {
        submit.disabled = false;
        submit.textContent = defaultLabel;
    }

    // Tombol lihat/sembunyikan password pada halaman login.
    if (toggle) {
        toggle.addEventListener('click', () => {
            const show = password.type === 'password';
            password.type = show ? 'text' : 'password';
            toggle.textContent = show ? 'Sembunyi' : 'Lihat';
            toggle.setAttribute('aria-pressed', String(show));
        });
    }

    [email, password].forEach((input) => {
        input.addEventListener('input', () => setError(input, false));
    });

    form.addEventListener('submit', (event) => {
        const invalidEmail = email.value.trim() === '';
        const invalidPassword = password.value === '';

        setError(email, invalidEmail);
        setError(password, invalidPassword);

        if (invalidEmail || invalidPassword) {
            event.preventDefault();
            (invalidEmail ? email : password).focus();
            return;
        }

        submit.disabled = true;
        submit.textContent = 'Memproses…';
    });

    // Pulihkan tombol jika halaman dikembalikan dari browser back-forward cache.
    window.addEventListener('pageshow', (event) => {
        if (event.persisted) resetSubmit();
    });
})();
