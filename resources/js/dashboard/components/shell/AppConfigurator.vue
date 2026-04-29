<script setup lang="ts">
import { ref, computed } from 'vue';
import Drawer from 'primevue/drawer';
import SelectButton from 'primevue/selectbutton';
import { updatePreset, updateSurfacePalette } from '@primevue/themes';
import Aura from '@primevue/themes/aura';
import Lara from '@primevue/themes/lara';
import Nora from '@primevue/themes/nora';
import { useLayout } from '@dashboard/composables/useLayout';
import { useAuthStore } from '@dashboard/stores/auth';

const { layoutConfig, layoutState, isDarkTheme, changeMenuMode, persistConfig } = useLayout();
const auth = useAuthStore();

const hideForClient = computed(() => auth.primaryRole === 'client');

type PresetName = 'Aura' | 'Lara' | 'Nora';
const presets: Record<PresetName, unknown> = { Aura, Lara, Nora };
const presetOptions = ['Aura', 'Lara', 'Nora'] as const;

const menuModeOptions = [
    { label: 'Static', value: 'static' },
    { label: 'Overlay', value: 'overlay' },
] as const;

type Palette = Record<string, string>;

const primaryColors: { name: string; palette: Palette }[] = [
    { name: 'emerald', palette: { 50: '#ecfdf5', 100: '#d1fae5', 200: '#a7f3d0', 300: '#6ee7b7', 400: '#34d399', 500: '#10b981', 600: '#059669', 700: '#047857', 800: '#065f46', 900: '#064e3b', 950: '#022c22' } },
    { name: 'teal',    palette: { 50: '#f0fdfa', 100: '#ccfbf1', 200: '#99f6e4', 300: '#5eead4', 400: '#2dd4bf', 500: '#14b8a6', 600: '#0d9488', 700: '#0f766e', 800: '#115e59', 900: '#134e4a', 950: '#042f2e' } },
    { name: 'sky',     palette: { 50: '#f0f9ff', 100: '#e0f2fe', 200: '#bae6fd', 300: '#7dd3fc', 400: '#38bdf8', 500: '#0ea5e9', 600: '#0284c7', 700: '#0369a1', 800: '#075985', 900: '#0c4a6e', 950: '#082f49' } },
    { name: 'blue',    palette: { 50: '#eff6ff', 100: '#dbeafe', 200: '#bfdbfe', 300: '#93c5fd', 400: '#60a5fa', 500: '#3b82f6', 600: '#2563eb', 700: '#1d4ed8', 800: '#1e40af', 900: '#1e3a8a', 950: '#172554' } },
    { name: 'indigo',  palette: { 50: '#eef2ff', 100: '#e0e7ff', 200: '#c7d2fe', 300: '#a5b4fc', 400: '#818cf8', 500: '#6366f1', 600: '#4f46e5', 700: '#4338ca', 800: '#3730a3', 900: '#312e81', 950: '#1e1b4b' } },
    { name: 'violet',  palette: { 50: '#f5f3ff', 100: '#ede9fe', 200: '#ddd6fe', 300: '#c4b5fd', 400: '#a78bfa', 500: '#8b5cf6', 600: '#7c3aed', 700: '#6d28d9', 800: '#5b21b6', 900: '#4c1d95', 950: '#2e1065' } },
    { name: 'fuchsia', palette: { 50: '#fdf4ff', 100: '#fae8ff', 200: '#f5d0fe', 300: '#f0abfc', 400: '#e879f9', 500: '#d946ef', 600: '#c026d3', 700: '#a21caf', 800: '#86198f', 900: '#701a75', 950: '#4a044e' } },
    { name: 'rose',    palette: { 50: '#fff1f2', 100: '#ffe4e6', 200: '#fecdd3', 300: '#fda4af', 400: '#fb7185', 500: '#f43f5e', 600: '#e11d48', 700: '#be123c', 800: '#9f1239', 900: '#881337', 950: '#4c0519' } },
    { name: 'amber',   palette: { 50: '#fffbeb', 100: '#fef3c7', 200: '#fde68a', 300: '#fcd34d', 400: '#fbbf24', 500: '#f59e0b', 600: '#d97706', 700: '#b45309', 800: '#92400e', 900: '#78350f', 950: '#451a03' } },
];

const surfaces: { name: string; palette: Palette }[] = [
    { name: 'slate',   palette: { 0: '#ffffff', 50: '#f8fafc', 100: '#f1f5f9', 200: '#e2e8f0', 300: '#cbd5e1', 400: '#94a3b8', 500: '#64748b', 600: '#475569', 700: '#334155', 800: '#1e293b', 900: '#0f172a', 950: '#020617' } },
    { name: 'gray',    palette: { 0: '#ffffff', 50: '#f9fafb', 100: '#f3f4f6', 200: '#e5e7eb', 300: '#d1d5db', 400: '#9ca3af', 500: '#6b7280', 600: '#4b5563', 700: '#374151', 800: '#1f2937', 900: '#111827', 950: '#030712' } },
    { name: 'zinc',    palette: { 0: '#ffffff', 50: '#fafafa', 100: '#f4f4f5', 200: '#e4e4e7', 300: '#d4d4d8', 400: '#a1a1aa', 500: '#71717a', 600: '#52525b', 700: '#3f3f46', 800: '#27272a', 900: '#18181b', 950: '#09090b' } },
    { name: 'neutral', palette: { 0: '#ffffff', 50: '#fafafa', 100: '#f5f5f5', 200: '#e5e5e5', 300: '#d4d4d4', 400: '#a3a3a3', 500: '#737373', 600: '#525252', 700: '#404040', 800: '#262626', 900: '#171717', 950: '#0a0a0a' } },
    { name: 'stone',   palette: { 0: '#ffffff', 50: '#fafaf9', 100: '#f5f5f4', 200: '#e7e5e4', 300: '#d6d3d1', 400: '#a8a29e', 500: '#78716c', 600: '#57534e', 700: '#44403c', 800: '#292524', 900: '#1c1917', 950: '#0c0a09' } },
];

const preset = ref<PresetName>(layoutConfig.preset);

function setPrimary(color: { name: string; palette: Palette }): void {
    layoutConfig.primary = color.name;
    updatePreset({
        semantic: {
            primary: color.palette,
            colorScheme: {
                light: {
                    primary: {
                        color: '{primary.500}',
                        contrastColor: '#ffffff',
                        hoverColor: '{primary.600}',
                        activeColor: '{primary.700}',
                    },
                    highlight: {
                        background: '{primary.50}',
                        focusBackground: '{primary.100}',
                        color: '{primary.700}',
                        focusColor: '{primary.800}',
                    },
                },
                dark: {
                    primary: {
                        color: '{primary.400}',
                        contrastColor: '{surface.900}',
                        hoverColor: '{primary.300}',
                        activeColor: '{primary.200}',
                    },
                    highlight: {
                        background: 'color-mix(in srgb, {primary.400}, transparent 84%)',
                        focusBackground: 'color-mix(in srgb, {primary.400}, transparent 76%)',
                        color: 'rgba(255,255,255,.87)',
                        focusColor: 'rgba(255,255,255,.87)',
                    },
                },
            },
        },
    });
    persistConfig();
}

function setSurface(surface: { name: string; palette: Palette }): void {
    layoutConfig.surface = surface.name;
    updateSurfacePalette(surface.palette);
    persistConfig();
}

function onPresetChange(): void {
    layoutConfig.preset = preset.value;
    updatePreset(presets[preset.value] as Record<string, unknown>);
    persistConfig();
}
</script>

<template>
    <Drawer
        v-if="!hideForClient"
        v-model:visible="layoutState.configSidebarVisible"
        position="right"
        class="w-80"
        header="Appearance"
    >
        <div class="flex flex-col gap-5">
            <div>
                <span class="text-sm font-semibold text-surface-700 dark:text-surface-100">Primary</span>
                <div class="pt-2 flex flex-wrap gap-2">
                    <button
                        v-for="color in primaryColors"
                        :key="color.name"
                        type="button"
                        :title="color.name"
                        class="w-6 h-6 rounded-full border-none cursor-pointer"
                        :class="{ 'ring-2 ring-offset-1 ring-primary-500': layoutConfig.primary === color.name }"
                        :style="{ backgroundColor: color.palette['500'] }"
                        @click="setPrimary(color)"
                    />
                </div>
            </div>

            <div>
                <span class="text-sm font-semibold text-surface-700 dark:text-surface-100">Surface</span>
                <div class="pt-2 flex flex-wrap gap-2">
                    <button
                        v-for="surface in surfaces"
                        :key="surface.name"
                        type="button"
                        :title="surface.name"
                        class="w-6 h-6 rounded-full border-none cursor-pointer"
                        :class="{ 'ring-2 ring-offset-1 ring-primary-500': layoutConfig.surface === surface.name || (!layoutConfig.surface && (isDarkTheme ? surface.name === 'zinc' : surface.name === 'slate')) }"
                        :style="{ backgroundColor: surface.palette['500'] }"
                        @click="setSurface(surface)"
                    />
                </div>
            </div>

            <div class="flex flex-col gap-2">
                <span class="text-sm font-semibold text-surface-700 dark:text-surface-100">Preset</span>
                <SelectButton
                    v-model="preset"
                    :options="[...presetOptions]"
                    :allow-empty="false"
                    @change="onPresetChange"
                />
            </div>

            <div class="flex flex-col gap-2">
                <span class="text-sm font-semibold text-surface-700 dark:text-surface-100">Menu Mode</span>
                <SelectButton
                    :model-value="layoutConfig.menuMode"
                    :options="[...menuModeOptions]"
                    option-label="label"
                    option-value="value"
                    :allow-empty="false"
                    @update:model-value="(v: 'static' | 'overlay') => changeMenuMode(v)"
                />
            </div>
        </div>
    </Drawer>
</template>
