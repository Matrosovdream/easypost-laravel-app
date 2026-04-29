import { createApp } from 'vue';
import { createPinia } from 'pinia';
import { createHead } from '@unhead/vue';
import PrimeVue from 'primevue/config';
import ToastService from 'primevue/toastservice';
import ConfirmationService from 'primevue/confirmationservice';
import 'primeicons/primeicons.css';

import { ShipDeskPreset } from '@shared/theme/preset';
import App from './App.vue';
import { router } from './router';

const app = createApp(App);

app.use(createPinia());
app.use(createHead());
app.use(router);
app.use(PrimeVue, {
    theme: { preset: ShipDeskPreset, options: { darkModeSelector: '.dark' } },
});
app.use(ToastService);
app.use(ConfirmationService);

app.mount('#web-app');
