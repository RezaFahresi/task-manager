import './echo';
import Alpine from 'alpinejs';
import * as bootstrap from 'bootstrap';
import { initNotifications } from './notifications';

window.Alpine = Alpine;
window.bootstrap = bootstrap;

Alpine.start();

if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', () => {
        initNotifications();
    });
} else {
    initNotifications();
}

