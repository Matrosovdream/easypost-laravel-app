<script setup lang="ts">
import { ref } from 'vue';
import Button from 'primevue/button';
import InputOtp from 'primevue/inputotp';
import Message from 'primevue/message';
import PageHeader from '@dashboard/components/home/PageHeader.vue';
import { profileApi } from '@dashboard/api/profile';
import { useToast } from '@dashboard/composables/useToast';

const toast = useToast();

const step = ref<'old' | 'new' | 'confirm' | 'done'>('old');
const oldPin = ref('');
const newPin = ref('');
const confirmPin = ref('');
const err = ref<string | null>(null);
const saving = ref(false);

function next(): void {
    err.value = null;
    if (step.value === 'old' && oldPin.value.length === 4) step.value = 'new';
    else if (step.value === 'new' && newPin.value.length === 4) step.value = 'confirm';
    else err.value = 'Enter a 4-digit PIN.';
}

async function submit(): Promise<void> {
    if (newPin.value !== confirmPin.value) { err.value = 'PINs do not match.'; return; }
    err.value = null;
    saving.value = true;
    try {
        await profileApi.changePin(oldPin.value, newPin.value);
        step.value = 'done';
        toast.success('PIN updated');
    } catch (e: unknown) {
        const r = e as { response?: { data?: { message?: string } } };
        err.value = r.response?.data?.message ?? 'Could not change PIN.';
    } finally { saving.value = false; }
}
</script>

<template>
    <div>
        <PageHeader title="Change PIN" subtitle="Your PIN is how you sign in. Keep it secret." />

        <div class="card max-w-md space-y-4 text-center">
            <Message v-if="err" severity="error" :closable="false">{{ err }}</Message>

            <template v-if="step === 'old'">
                <div>Enter your current PIN</div>
                <InputOtp v-model="oldPin" :length="4" integer-only mask class="justify-center" />
                <Button label="Next" class="w-full" @click="next" />
            </template>
            <template v-else-if="step === 'new'">
                <div>Enter a new 4-digit PIN</div>
                <InputOtp v-model="newPin" :length="4" integer-only mask class="justify-center" />
                <Button label="Next" class="w-full" @click="next" />
            </template>
            <template v-else-if="step === 'confirm'">
                <div>Re-enter the new PIN</div>
                <InputOtp v-model="confirmPin" :length="4" integer-only mask class="justify-center" />
                <Button label="Change PIN" class="w-full" :loading="saving" @click="submit" />
            </template>
            <template v-else>
                <Message severity="success" :closable="false">PIN changed. Use your new PIN next time you sign in.</Message>
                <router-link to="/dashboard/profile">
                    <Button label="Back to profile" severity="secondary" outlined class="w-full" />
                </router-link>
            </template>
        </div>
    </div>
</template>
