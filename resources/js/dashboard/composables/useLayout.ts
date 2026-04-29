import { computed, reactive } from 'vue';

type LayoutConfig = {
    preset: 'Aura' | 'Lara' | 'Nora';
    primary: string;
    surface: string | null;
    darkTheme: boolean;
    menuMode: 'static' | 'overlay';
};

type LayoutState = {
    staticMenuInactive: boolean;
    overlayMenuActive: boolean;
    profileSidebarVisible: boolean;
    configSidebarVisible: boolean;
    mobileMenuActive: boolean;
    menuHoverActive: boolean;
    activeMenuItem: string | null;
    activePath: string | null;
};

const STORAGE_KEY = 'shipdesk.layout';

type Persisted = Partial<Pick<LayoutConfig, 'preset' | 'primary' | 'surface' | 'darkTheme' | 'menuMode'>>;

function readPersisted(): Persisted {
    try {
        const raw = localStorage.getItem(STORAGE_KEY);
        return raw ? (JSON.parse(raw) as Persisted) : {};
    } catch {
        return {};
    }
}

function writePersisted(config: LayoutConfig): void {
    try {
        localStorage.setItem(STORAGE_KEY, JSON.stringify({
            preset: config.preset,
            primary: config.primary,
            surface: config.surface,
            darkTheme: config.darkTheme,
            menuMode: config.menuMode,
        }));
    } catch {
        // ignore
    }
}

const persisted = readPersisted();

const layoutConfig = reactive<LayoutConfig>({
    preset: persisted.preset ?? 'Aura',
    primary: persisted.primary ?? 'indigo',
    surface: persisted.surface ?? null,
    darkTheme: persisted.darkTheme ?? false,
    menuMode: persisted.menuMode ?? 'static',
});

const layoutState = reactive<LayoutState>({
    staticMenuInactive: false,
    overlayMenuActive: false,
    profileSidebarVisible: false,
    configSidebarVisible: false,
    mobileMenuActive: false,
    menuHoverActive: false,
    activeMenuItem: null,
    activePath: null,
});

if (typeof document !== 'undefined' && layoutConfig.darkTheme) {
    document.documentElement.classList.add('app-dark');
}

export function useLayout() {
    const isDesktop = (): boolean => window.innerWidth > 991;

    const executeDarkModeToggle = (): void => {
        layoutConfig.darkTheme = !layoutConfig.darkTheme;
        document.documentElement.classList.toggle('app-dark');
        writePersisted(layoutConfig);
    };

    const toggleDarkMode = (): void => {
        const hasViewTransition =
            typeof document !== 'undefined' && 'startViewTransition' in document;
        if (!hasViewTransition) {
            executeDarkModeToggle();
            return;
        }
        (document as Document & { startViewTransition: (cb: () => void) => void })
            .startViewTransition(() => executeDarkModeToggle());
    };

    const toggleMenu = (): void => {
        if (isDesktop()) {
            if (layoutConfig.menuMode === 'static') {
                layoutState.staticMenuInactive = !layoutState.staticMenuInactive;
            } else {
                layoutState.overlayMenuActive = !layoutState.overlayMenuActive;
            }
        } else {
            layoutState.mobileMenuActive = !layoutState.mobileMenuActive;
        }
    };

    const toggleConfigSidebar = (): void => {
        layoutState.configSidebarVisible = !layoutState.configSidebarVisible;
    };

    const hideMobileMenu = (): void => {
        layoutState.mobileMenuActive = false;
        layoutState.overlayMenuActive = false;
    };

    const changeMenuMode = (ev: { value?: 'static' | 'overlay' } | 'static' | 'overlay'): void => {
        const mode = typeof ev === 'string' ? ev : ev.value;
        if (!mode) return;
        layoutConfig.menuMode = mode;
        layoutState.staticMenuInactive = false;
        layoutState.overlayMenuActive = false;
        layoutState.mobileMenuActive = false;
        layoutState.menuHoverActive = false;
        writePersisted(layoutConfig);
    };

    const isDarkTheme = computed(() => layoutConfig.darkTheme);
    const hasOpenOverlay = computed(() => layoutState.overlayMenuActive);

    return {
        layoutConfig,
        layoutState,
        isDarkTheme,
        hasOpenOverlay,
        isDesktop,
        toggleDarkMode,
        toggleMenu,
        toggleConfigSidebar,
        hideMobileMenu,
        changeMenuMode,
        persistConfig: () => writePersisted(layoutConfig),
    };
}
