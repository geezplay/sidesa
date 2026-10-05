import './bootstrap';
import Alpine from 'alpinejs';
import { read, utils } from 'xlsx';
import { siadesaStore } from './store';

window.Alpine = Alpine;
window.siadesaStore = siadesaStore;
window.XLSX = { read, utils };

// Muat data dari backend sebelum aplikasi dijalankan
siadesaStore.bootstrap().finally(() => {
    Alpine.start();
});
