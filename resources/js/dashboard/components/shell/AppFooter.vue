<script setup lang="ts">
const year = new Date().getFullYear();
</script>

<template>
    <footer class="layout-footer">
        <span>&copy; {{ year }} ShipDesk</span>
        <span>
            <a href="/portal/terms">Terms</a> ·
            <a href="/portal/privacy">Privacy</a> ·
            <a href="mailto:help@shipdesk.local">Help</a>
        </span>
    </footer>
</template>
