import { ref, onMounted } from 'vue';

const theme = ref<'dark' | 'light'>('dark');
const borderWidth = ref<'2px' | '1px'>('2px');
const accentColor = ref<string>('#10b981');

const hexToRgb = (hex: string) => {
    const clean = hex.replace('#', '');
    return {
        r: parseInt(clean.substring(0, 2), 16) || 0,
        g: parseInt(clean.substring(2, 4), 16) || 0,
        b: parseInt(clean.substring(4, 6), 16) || 0,
    };
};

const lightenColor = (hex: string, percent = 28): string => {
    const num = parseInt(hex.replace('#', ''), 16);
    const amt = Math.round(2.55 * percent);
    const R = Math.min(255, (num >> 16) + amt);
    const G = Math.min(255, ((num >> 8) & 0x00ff) + amt);
    const B = Math.min(255, (num & 0x0000ff) + amt);
    return `#${(0x1000000 + (R << 16) + (G << 8) + B).toString(16).slice(1)}`;
};

export function useTheme() {
    const applyBorderWidth = (width: '2px' | '1px') => {
        borderWidth.value = width;
        if (typeof document !== 'undefined') {
            document.documentElement.style.setProperty('--stroke-w', width);
            localStorage.setItem('sgen-border-width', width);
        }
    };

    const toggleBorderWidth = () => {
        const next = borderWidth.value === '2px' ? '1px' : '2px';
        applyBorderWidth(next);
    };

    const applyAccentColor = (hex: string) => {
        accentColor.value = hex;
        if (typeof document !== 'undefined') {
            const light = lightenColor(hex, 28);
            const { r, g, b } = hexToRgb(hex);

            document.documentElement.style.setProperty('--orange', hex);
            document.documentElement.style.setProperty('--orange-light', light);
            document.documentElement.style.setProperty('--orange-rgb', `${r}, ${g}, ${b}`);
            document.documentElement.style.setProperty('--accent-soft', `rgba(${r}, ${g}, ${b}, 0.12)`);
            document.documentElement.style.setProperty('--border-focus', hex);
            document.documentElement.style.setProperty('--color-primary', hex);
            document.documentElement.style.setProperty('--color-primary-hover', light);

            localStorage.setItem('sgen-accent-color', hex);
        }
    };

    const applyTheme = (mode: 'dark' | 'light') => {
        theme.value = mode;
        if (typeof document !== 'undefined') {
            const isLight = mode === 'light';
            document.body.classList.toggle('light', isLight);
            document.documentElement.classList.toggle('light', isLight);
            if (isLight) {
                document.documentElement.setAttribute('data-theme', 'light');
            } else {
                document.documentElement.removeAttribute('data-theme');
            }
            localStorage.setItem('sgen-theme', mode);
            localStorage.setItem('inv-theme-mode', mode);
        }
    };

    const toggleTheme = () => {
        const next = theme.value === 'dark' ? 'light' : 'dark';
        applyTheme(next);
    };

    const initTheme = () => {
        if (typeof window === 'undefined') return;

        const savedTheme = (localStorage.getItem('inv-theme-mode') || localStorage.getItem('sgen-theme')) as 'dark' | 'light' | null;
        if (savedTheme) {
            applyTheme(savedTheme);
        }

        const savedBorder = (localStorage.getItem('inv-border-width') || localStorage.getItem('sgen-border-width')) as '2px' | '1px' | null;
        if (savedBorder) {
            applyBorderWidth(savedBorder);
        }

        const savedAccent = localStorage.getItem('sgen-accent-color');
        if (savedAccent) {
            applyAccentColor(savedAccent);
        } else {
            applyAccentColor(accentColor.value);
        }
    };

    return {
        theme,
        borderWidth,
        accentColor,
        applyBorderWidth,
        toggleBorderWidth,
        applyAccentColor,
        applyTheme,
        toggleTheme,
        initTheme,
    };
}
