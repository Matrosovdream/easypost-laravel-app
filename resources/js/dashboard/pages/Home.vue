<script setup lang="ts">
import { computed, defineAsyncComponent } from 'vue';
import { useAuthStore } from '@dashboard/stores/auth';

const AdminHome    = defineAsyncComponent(() => import('./home/AdminHome.vue'));
const ManagerHome  = defineAsyncComponent(() => import('./home/ManagerHome.vue'));
const ShipperHome  = defineAsyncComponent(() => import('./home/ShipperHome.vue'));
const CsAgentHome  = defineAsyncComponent(() => import('./home/CsAgentHome.vue'));
const ClientHome   = defineAsyncComponent(() => import('./home/ClientHome.vue'));
const ViewerHome   = defineAsyncComponent(() => import('./home/ViewerHome.vue'));

const auth = useAuthStore();

const home = computed(() => {
    switch (auth.primaryRole) {
        case 'admin':    return AdminHome;
        case 'manager':  return ManagerHome;
        case 'shipper':  return ShipperHome;
        case 'cs_agent': return CsAgentHome;
        case 'client':   return ClientHome;
        default:         return ViewerHome;
    }
});
</script>

<template>
    <component :is="home" />
</template>
