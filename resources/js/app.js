import './bootstrap';
import './auth';

import Alpine from 'alpinejs';

import userMenu from './components/user-menu';
import flashMessage from './components/flash-message';
import confirmAction from './components/confirm-action';
import copyText from './components/copy-text';
import passwordToggle from './components/password-toggle';

import masterCrud from './pages/master';
import staffBooking from './pages/booking';
import bookingStatus from './pages/booking-status';
import appointmentManager from './pages/appointments';

// Registrasi Alpine Components
Alpine.data('userMenu', userMenu);
Alpine.data('flashMessage', flashMessage);
Alpine.data('confirmAction', confirmAction);
Alpine.data('copyText', copyText);
Alpine.data('passwordToggle', passwordToggle);

Alpine.data('masterCrud', masterCrud);
Alpine.data('staffBooking', staffBooking);
Alpine.data('bookingStatus', bookingStatus);
Alpine.data('appointmentManager', appointmentManager);

window.Alpine = Alpine;
Alpine.start();