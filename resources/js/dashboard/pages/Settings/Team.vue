<script setup lang="ts">
import { ref, reactive, onMounted } from 'vue';
import InputText from 'primevue/inputtext';
import Button from 'primevue/button';
import Tag from 'primevue/tag';
import PageHeader from '@dashboard/components/home/PageHeader.vue';
import { settingsApi } from '@dashboard/api/settings';
import { useToast } from '@dashboard/composables/useToast';

const toast = useToast();
const team = ref<Awaited<ReturnType<typeof settingsApi.team>> | null>(null);
const form = reactive({ name: '', time_zone: '', default_currency: 'USD' });
const saving = ref(false);

async function load(): Promise<void> {
    team.value = await settingsApi.team();
    form.name = team.value.name;
    form.time_zone = team.value.time_zone;
    form.default_currency = team.value.default_currency;
}

async function save(): Promise<void> {
    saving.value = true;
    try {
        await settingsApi.updateTeam({
            name: form.name,
            time_zone: form.time_zone,
            default_currency: form.default_currency,
        });
        toast.success('Saved');
        await load();
    } catch { toast.error('Could not save.'); }
    finally { saving.value = false; }
}

onMounted(load);
</script>

<template>
    <div>
        <PageHeader title="Team settings" subtitle="Your tenant profile and defaults." />

        <div v-if="team" class="card max-w-2xl space-y-4">
            <div class="flex items-center gap-2">
                <Tag :value="`Plan: ${team.plan}`" />
                <Tag :value="`Mode: ${team.mode}`" />
                <Tag :value="team.status" :severity="team.status === 'active' ? 'success' : 'warn'" />
            </div>

            <div>
                <label class="block text-sm font-medium mb-1">Team name</label>
                <InputText v-model="form.name" class="w-full" />
            </div>
            <div class="grid md:grid-cols-2 gap-3">
                <div>
                    <label class="block text-sm font-medium mb-1">Time zone</label>
                    <InputText v-model="form.time_zone" class="w-full" />
                </div>
                <div>
                    <label class="block text-sm font-medium mb-1">Default currency</label>
                    <InputText v-model="form.default_currency" maxlength="3" class="w-full" />
                </div>
            </div>

            <div class="flex justify-end">
                <Button label="Save" :loading="saving" @click="save" />
            </div>
        </div>
    </div>
</template>
