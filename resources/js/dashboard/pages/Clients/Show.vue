<script setup lang="ts">
import { ref, onMounted } from 'vue';
import { useRoute } from 'vue-router';
import InputNumber from 'primevue/inputnumber';
import Button from 'primevue/button';
import Tag from 'primevue/tag';
import Tabs from 'primevue/tabs';
import TabList from 'primevue/tablist';
import Tab from 'primevue/tab';
import TabPanels from 'primevue/tabpanels';
import TabPanel from 'primevue/tabpanel';
import PageHeader from '@dashboard/components/home/PageHeader.vue';
import { clientsApi, type ClientItem, type InvoiceResp } from '@dashboard/api/clients';
import { useToast } from '@dashboard/composables/useToast';

const route = useRoute();
const toast = useToast();

const c = ref<ClientItem | null>(null);
const markup = ref(0);
const busy = ref(false);
const invoice = ref<InvoiceResp | null>(null);

async function load(): Promise<void> {
    c.value = await clientsApi.show(Number(route.params.id));
    markup.value = c.value.flexrate_markup_pct;
}

function money(cents: number): string { return `$${(cents / 100).toFixed(2)}`; }

async function saveMarkup(): Promise<void> {
    if (!c.value) return;
    busy.value = true;
    try {
        c.value = await clientsApi.setFlexRate(c.value.id, markup.value);
        toast.success('FlexRate updated');
    } catch { toast.error('Could not save.'); }
    finally { busy.value = false; }
}

async function generateInvoice(): Promise<void> {
    if (!c.value) return;
    busy.value = true;
    try {
        const from = new Date(); from.setDate(from.getDate() - 30);
        const to = new Date();
        invoice.value = await clientsApi.invoice(c.value.id, from.toISOString().slice(0, 10), to.toISOString().slice(0, 10));
        toast.success(`Invoice generated — ${invoice.value.totals.count} lines`);
    } catch { toast.error('Could not generate invoice.'); }
    finally { busy.value = false; }
}

onMounted(load);
</script>

<template>
    <div v-if="c">
        <PageHeader :title="c.company_name" :subtitle="c.contact_name ?? undefined">
            <template #actions>
                <router-link to="/dashboard/clients">
                    <Button label="Back" severity="secondary" text />
                </router-link>
                <Tag :value="c.status" />
            </template>
        </PageHeader>

        <Tabs value="overview">
            <TabList>
                <Tab value="overview">Overview</Tab>
                <Tab value="pricing">Pricing</Tab>
                <Tab value="invoices">Invoices</Tab>
            </TabList>
            <TabPanels>
                <TabPanel value="overview">
                    <div class="grid md:grid-cols-2 gap-4">
                        <div class="card">
                            <h3 class="font-semibold mb-2">Contact</h3>
                            <div class="text-sm space-y-1">
                                <div>{{ c.contact_name ?? '—' }}</div>
                                <div>{{ c.contact_email ?? '' }}</div>
                                <div>{{ c.contact_phone ?? '' }}</div>
                            </div>
                        </div>
                        <div class="card">
                            <h3 class="font-semibold mb-2">Billing</h3>
                            <div class="text-sm">
                                {{ c.billing_mode }} · Net {{ c.credit_terms_days }} days<br>
                                FlexRate: {{ c.flexrate_markup_pct }}%<br>
                                EP endshipper: <span class="font-mono">{{ c.ep_endshipper_id ?? '— (needs attention)' }}</span>
                            </div>
                        </div>
                    </div>
                </TabPanel>

                <TabPanel value="pricing">
                    <div class="card max-w-md">
                        <h3 class="font-semibold mb-3">FlexRate markup</h3>
                        <div class="flex gap-3 items-end">
                            <div class="flex-1">
                                <label class="block text-sm font-medium mb-1">Percentage markup</label>
                                <InputNumber v-model="markup" :min="0" :max="100" :max-fraction-digits="2" suffix="%" class="w-full" />
                            </div>
                            <Button label="Save" :loading="busy" @click="saveMarkup" />
                        </div>
                    </div>
                </TabPanel>

                <TabPanel value="invoices">
                    <div class="card mb-4">
                        <Button label="Generate last-30-day invoice" icon="pi pi-file-pdf" :loading="busy" @click="generateInvoice" />
                    </div>
                    <div v-if="invoice" class="card">
                        <h3 class="font-semibold mb-3">
                            Invoice · {{ invoice.totals.count }} shipments
                        </h3>
                        <div class="grid md:grid-cols-3 gap-4 mb-4">
                            <div>
                                <div class="text-xs text-surface-500 uppercase">Carrier cost</div>
                                <div class="text-lg font-bold">{{ money(invoice.totals.carrier_cost_cents) }}</div>
                            </div>
                            <div>
                                <div class="text-xs text-surface-500 uppercase">Markup</div>
                                <div class="text-lg font-bold text-primary-600">{{ money(invoice.totals.markup_cents) }}</div>
                            </div>
                            <div>
                                <div class="text-xs text-surface-500 uppercase">Total charge</div>
                                <div class="text-2xl font-bold">{{ money(invoice.totals.charge_cents) }}</div>
                            </div>
                        </div>
                        <div class="overflow-x-auto">
                            <table class="min-w-full text-sm">
                                <thead class="text-xs uppercase text-surface-500">
                                    <tr>
                                        <th class="text-left py-2">Shipment</th>
                                        <th class="text-left">Carrier</th>
                                        <th class="text-right">Cost</th>
                                        <th class="text-right">Markup</th>
                                        <th class="text-right">Charge</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr v-for="l in invoice.lines" :key="l.shipment_id" class="border-t border-surface-100">
                                        <td class="py-2">#{{ l.shipment_id }} {{ l.reference ?? '' }}</td>
                                        <td>{{ l.carrier }} · {{ l.service }}</td>
                                        <td class="text-right">{{ money(l.carrier_cost_cents) }}</td>
                                        <td class="text-right">{{ money(l.markup_cents) }} ({{ l.markup_pct }}%)</td>
                                        <td class="text-right font-semibold">{{ money(l.charge_cents) }}</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </TabPanel>
            </TabPanels>
        </Tabs>
    </div>
</template>
