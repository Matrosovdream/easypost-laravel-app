<script setup lang="ts">
import { ref, reactive, computed } from 'vue';
import { useRouter } from 'vue-router';
import Button from 'primevue/button';
import Stepper from 'primevue/stepper';
import StepList from 'primevue/steplist';
import Step from 'primevue/step';
import StepItem from 'primevue/stepitem';
import StepPanel from 'primevue/steppanel';
import StepPanels from 'primevue/steppanels';
import InputText from 'primevue/inputtext';
import InputNumber from 'primevue/inputnumber';
import Message from 'primevue/message';
import PageHeader from '@dashboard/components/home/PageHeader.vue';
import { shipmentsApi } from '@dashboard/api/shipments';
import type { Address, ShipmentDetail } from '@dashboard/types/shipment';
import { useToast } from '@dashboard/composables/useToast';

const router = useRouter();
const toast = useToast();

const active = ref(0);
const busy = ref(false);
const error = ref<string | null>(null);
const created = ref<ShipmentDetail | null>(null);

const from = reactive<Address>({
    name: 'ShipDesk Warehouse',
    company: 'ShipDesk',
    street1: '417 Montgomery St',
    city: 'San Francisco',
    state: 'CA',
    zip: '94104',
    country: 'US',
    phone: '4155550100',
    email: 'ops@shipdesk.local',
});

const to = reactive<Address>({
    name: '',
    street1: '',
    city: '',
    state: '',
    zip: '',
    country: 'US',
    phone: '',
    email: '',
});

const parcel = reactive({
    weight_oz: 16,
    length_in: 10,
    width_in: 8,
    height_in: 4,
});

const reference = ref('');

const canNext0 = computed(() => !!to.street1 && !!to.country && !!to.zip);

async function submit(): Promise<void> {
    busy.value = true;
    error.value = null;
    try {
        created.value = await shipmentsApi.create({
            to_address: to,
            from_address: from,
            parcel: {
                weight_oz: parcel.weight_oz,
                length_in: parcel.length_in,
                width_in: parcel.width_in,
                height_in: parcel.height_in,
            },
            reference: reference.value || undefined,
        });
        toast.success('Shipment created');
        active.value = 2;
    } catch (e: unknown) {
        const r = e as { response?: { data?: { message?: string } } };
        error.value = r.response?.data?.message ?? 'Could not create shipment.';
    } finally {
        busy.value = false;
    }
}
</script>

<template>
    <div>
        <PageHeader title="New shipment" subtitle="To + From, parcel, then rate shop." />

        <div class="bg-white rounded-xl border border-surface-200 p-6">
            <Stepper v-model:value="active" linear>
                <StepList>
                    <Step :value="0">Addresses</Step>
                    <Step :value="1">Parcel</Step>
                    <Step :value="2">Review & rate-shop</Step>
                </StepList>
                <StepPanels>
                    <StepPanel :value="0">
                        <div class="grid md:grid-cols-2 gap-6">
                            <div>
                                <h3 class="font-semibold text-surface-900 mb-3">Ship to</h3>
                                <div class="space-y-3">
                                    <InputText v-model="to.name" placeholder="Recipient name" class="w-full" />
                                    <InputText v-model="to.street1" placeholder="Street 1" class="w-full" />
                                    <InputText v-model="to.street2" placeholder="Street 2 (optional)" class="w-full" />
                                    <div class="grid grid-cols-2 gap-3">
                                        <InputText v-model="to.city" placeholder="City" />
                                        <InputText v-model="to.state" placeholder="State" />
                                    </div>
                                    <div class="grid grid-cols-2 gap-3">
                                        <InputText v-model="to.zip" placeholder="ZIP" />
                                        <InputText v-model="to.country" placeholder="Country (US)" maxlength="2" />
                                    </div>
                                </div>
                            </div>
                            <div>
                                <h3 class="font-semibold text-surface-900 mb-3">Ship from</h3>
                                <div class="space-y-3">
                                    <InputText v-model="from.name" class="w-full" />
                                    <InputText v-model="from.street1" class="w-full" />
                                    <div class="grid grid-cols-2 gap-3">
                                        <InputText v-model="from.city" />
                                        <InputText v-model="from.state" />
                                    </div>
                                    <div class="grid grid-cols-2 gap-3">
                                        <InputText v-model="from.zip" />
                                        <InputText v-model="from.country" maxlength="2" />
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="flex justify-end mt-6 gap-2">
                            <Button label="Continue" icon="pi pi-arrow-right" icon-pos="right" :disabled="!canNext0" @click="active = 1" />
                        </div>
                    </StepPanel>

                    <StepPanel :value="1">
                        <h3 class="font-semibold text-surface-900 mb-3">Parcel</h3>
                        <div class="grid md:grid-cols-4 gap-3 max-w-2xl">
                            <label class="block">
                                <span class="text-sm text-surface-600">Weight (oz)</span>
                                <InputNumber v-model="parcel.weight_oz" :min-fraction-digits="0" :max-fraction-digits="2" class="w-full" />
                            </label>
                            <label class="block">
                                <span class="text-sm text-surface-600">Length (in)</span>
                                <InputNumber v-model="parcel.length_in" :min-fraction-digits="0" :max-fraction-digits="2" class="w-full" />
                            </label>
                            <label class="block">
                                <span class="text-sm text-surface-600">Width (in)</span>
                                <InputNumber v-model="parcel.width_in" :min-fraction-digits="0" :max-fraction-digits="2" class="w-full" />
                            </label>
                            <label class="block">
                                <span class="text-sm text-surface-600">Height (in)</span>
                                <InputNumber v-model="parcel.height_in" :min-fraction-digits="0" :max-fraction-digits="2" class="w-full" />
                            </label>
                        </div>
                        <label class="block mt-6 max-w-sm">
                            <span class="text-sm text-surface-600">Reference (optional)</span>
                            <InputText v-model="reference" class="w-full" />
                        </label>
                        <div class="flex justify-between mt-6">
                            <Button label="Back" severity="secondary" outlined @click="active = 0" />
                            <Button
                                label="Rate shop"
                                :loading="busy"
                                icon="pi pi-arrow-right"
                                icon-pos="right"
                                @click="submit"
                            />
                        </div>
                    </StepPanel>

                    <StepPanel :value="2">
                        <Message v-if="error" severity="error" :closable="false">{{ error }}</Message>
                        <div v-else-if="created">
                            <Message severity="success" :closable="false">
                                Shipment #{{ created.id }} created. {{ created.rates.length }} rate{{ created.rates.length === 1 ? '' : 's' }} back.
                            </Message>
                            <div class="flex gap-2 mt-4">
                                <Button label="Open shipment" icon="pi pi-arrow-right" icon-pos="right" @click="router.push(`/dashboard/shipments/${created!.id}`)" />
                                <Button label="Back to list" severity="secondary" outlined @click="router.push('/dashboard/shipments')" />
                            </div>
                        </div>
                    </StepPanel>
                </StepPanels>
            </Stepper>
        </div>
    </div>
</template>
