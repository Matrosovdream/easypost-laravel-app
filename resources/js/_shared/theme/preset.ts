import { definePreset } from '@primevue/themes';
import Aura from '@primevue/themes/aura';

export const ShipDeskPreset = definePreset(Aura, {
    semantic: {
        primary: {
            50:  '#eef4fc',
            100: '#d7e4f7',
            200: '#afc9ee',
            300: '#7ea8e0',
            400: '#4a83cf',
            500: '#1f60bd',
            600: '#0e4da4',
            700: '#0b3d8b',
            800: '#0a3273',
            900: '#0a295c',
            950: '#061937',
        },
        colorScheme: {
            light: {
                surface: {
                    0:   '#ffffff',
                    50:  '#f6f8fc',
                    100: '#edf1f8',
                    200: '#dce3ee',
                    300: '#b9c5da',
                    400: '#8697b6',
                    500: '#5a6c8e',
                    600: '#3f506f',
                    700: '#2a3a56',
                    800: '#17233a',
                    900: '#0a1a33',
                    950: '#04101f',
                },
            },
            dark: {
                surface: {
                    0:   '#05101f',
                    50:  '#0a1a33',
                    100: '#17233a',
                    200: '#2a3a56',
                    300: '#3f506f',
                    400: '#5a6c8e',
                    500: '#8697b6',
                    600: '#b9c5da',
                    700: '#dce3ee',
                    800: '#edf1f8',
                    900: '#f6f8fc',
                    950: '#ffffff',
                },
            },
        },
    },
});
