import { createApp } from 'vue';
import { createPinia } from 'pinia';
import PrimeVue from 'primevue/config';
import ToastService from 'primevue/toastservice';
import ConfirmationService from 'primevue/confirmationservice';
import 'primeicons/primeicons.css';
import '../../css/dashboard-sakai.css';

import { ShipDeskPreset } from '@shared/theme/preset';
import App from './App.vue';
import { router } from './router';

const app = createApp(App);

app.use(createPinia());
app.use(router);
app.use(PrimeVue, {
    theme: { preset: ShipDeskPreset, options: { darkModeSelector: '.app-dark' } },
});
app.use(ToastService);
app.use(ConfirmationService);

app.mount('#dashboard-app');
