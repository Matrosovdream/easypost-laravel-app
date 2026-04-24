<script setup lang="ts">
import { ref, reactive, onMounted } from 'vue';
import InputText from 'primevue/inputtext';
import Button from 'primevue/button';
import PageHeader from '@dashboard/components/home/PageHeader.vue';
import { useAuthStore } from '@dashboard/stores/auth';
import { profileApi } from '@dashboard/api/profile';
import { useToast } from '@dashboard/composables/useToast';

const auth = useAuthStore();
const toast = useToast();

const form = reactive({
    name: '',
    phone: '',
    locale: 'en',
    timezone: 'UTC',
});
const saving = ref(false);

onMounted(() => {
    form.name = auth.user?.name ?? '';
    form.phone = '';
    form.locale = auth.user?.locale ?? 'en';
    form.timezone = auth.user?.timezone ?? 'UTC';
});

async function save(): Promise<void> {
    saving.value = true;
    try {
        await profileApi.update(form);
        await auth.fetchMe();
        toast.success('Profile saved');
    } catch { toast.error('Could not save.'); }
    finally { saving.value = false; }
}
</script>

<template>
    <div>
        <PageHeader title="Profile" subtitle="Your display info." />

        <div class="card max-w-xl space-y-4">
            <div>
                <label class="block text-sm font-medium mb-1">Name</label>
                <InputText v-model="form.name" class="w-full" />
            </div>
            <div>
                <label class="block text-sm font-medium mb-1">Email</label>
                <InputText :model-value="auth.user?.email ?? ''" disabled class="w-full" />
            </div>
            <div>
                <label class="block text-sm font-medium mb-1">Phone</label>
                <InputText v-model="form.phone" class="w-full" />
            </div>
            <div class="grid md:grid-cols-2 gap-3">
                <div>
                    <label class="block text-sm font-medium mb-1">Locale</label>
                    <InputText v-model="form.locale" class="w-full" maxlength="16" />
                </div>
                <div>
                    <label class="block text-sm font-medium mb-1">Timezone</label>
                    <InputText v-model="form.timezone" class="w-full" maxlength="64" />
                </div>
            </div>
            <div class="flex justify-end">
                <Button label="Save" :loading="saving" @click="save" />
            </div>
        </div>

        <div class="mt-4 flex gap-3">
            <router-link to="/dashboard/profile/pin">
                <Button label="Change PIN" icon="pi pi-lock" severity="secondary" />
            </router-link>
            <router-link to="/dashboard/profile/security">
                <Button label="Security" icon="pi pi-shield" severity="secondary" />
            </router-link>
            <router-link to="/dashboard/profile/notifications">
                <Button label="Notifications" icon="pi pi-bell" severity="secondary" />
            </router-link>
        </div>
    </div>
</template>
