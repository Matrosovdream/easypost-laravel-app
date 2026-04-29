<script setup lang="ts">
import { computed } from 'vue';
import { nav, type NavItem, type NavSection } from '@dashboard/config/nav';
import { useCan } from '@dashboard/composables/useCan';
import AppMenuItem from './AppMenuItem.vue';

const { can, canAny, hasAnyRole } = useCan();

function isVisible(item: NavItem): boolean {
    if (item.right && !can(item.right)) return false;
    if (item.anyRight && !canAny(item.anyRight)) return false;
    if (item.roles && !hasAnyRole(item.roles)) return false;
    if (item.hideFromRoles && hasAnyRole(item.hideFromRoles)) return false;
    return true;
}

function sectionVisible(section: NavSection): boolean {
    if (section.roles && !hasAnyRole(section.roles)) return false;
    if (section.hideFromRoles && hasAnyRole(section.hideFromRoles)) return false;
    return true;
}

const filteredSections = computed<NavSection[]>(() =>
    nav
        .filter(sectionVisible)
        .map((section) => ({
            ...section,
            items: section.items.filter(isVisible),
        }))
        .filter((section) => section.items.length > 0),
);
</script>

<template>
    <ul class="layout-menu">
        <li
            v-for="section in filteredSections"
            :key="section.label"
            class="layout-root-menuitem"
        >
            <div class="layout-menuitem-root-text">{{ section.label }}</div>
            <ul style="list-style: none; margin: 0; padding: 0;">
                <AppMenuItem
                    v-for="item in section.items"
                    :key="item.label + (item.to ?? '')"
                    :item="item"
                />
            </ul>
        </li>
    </ul>
</template>
