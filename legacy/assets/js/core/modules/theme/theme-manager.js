import storageManager, { STORAGE_KEYS } from '../storage/storage-manager.js';

/**
 * Theme Manager - Sistema unificado de manejo de temas
 * Administra el cambio entre modo claro y oscuro de manera consistente
 */
class ThemeManager {
    constructor() {
        this.storageManager = storageManager;
        this.currentTheme = this.getInitialTheme();
        this.observers = new Set();
        this.init();
    }

    /**
     * Obtiene el tema inicial basado en localStorage o preferencia del sistema
     * Por defecto usa modo oscuro
     */
    getInitialTheme() {
        const savedTheme = this.storageManager.getTheme();
        if (savedTheme && ['light', 'dark'].includes(savedTheme)) {
            return savedTheme;
        }
        
        // Defaultea a dark mode siempre
        const prefersDark = window.matchMedia('(prefers-color-scheme: dark)').matches;
        return prefersDark ? 'dark' : 'dark'; // Default to dark always
    }

    /**
     * Inicializa el sistema de temas
     */
    init() {
        this.applyTheme(this.currentTheme);
        this.setupSystemPreferenceListener();
    }

    /**
     * Aplica el tema especificado
     */
    applyTheme(theme) {
        // Aplicar en html para variables CSS
        if (theme === 'dark') {
            document.documentElement.classList.remove('light');
            document.documentElement.classList.add('dark');
        } else {
            document.documentElement.classList.remove('dark');
            document.documentElement.classList.add('light');
        }
        
        // Aplicar en body para estilos específicos
        if (document.body) {
            if (theme === 'dark') {
                document.body.classList.remove('light');
                document.body.classList.add('dark');
            } else {
                document.body.classList.remove('dark');
                document.body.classList.add('light');
            }
        }

        // Set color-scheme for better browser support
        document.documentElement.style.colorScheme = theme;

        this.currentTheme = theme;
        this.storageManager.setTheme(theme);
        this.notifyObservers(theme);
    }

    /**
     * Cambia entre modo claro y oscuro
     */
    toggle() {
        const newTheme = this.currentTheme === 'dark' ? 'light' : 'dark';
        this.applyTheme(newTheme);
        return newTheme;
    }

    /**
     * Obtiene el tema actual
     */
    getCurrentTheme() {
        return this.currentTheme;
    }

    /**
     * Configura el listener para cambios en la preferencia del sistema
     */
    setupSystemPreferenceListener() {
        const mediaQuery = window.matchMedia('(prefers-color-scheme: dark)');
        mediaQuery.addEventListener('change', (e) => {
            // Solo aplicar si no hay tema guardado explícitamente
            if (!this.storageManager.has(STORAGE_KEYS.THEME)) {
                this.applyTheme(e.matches ? 'dark' : 'light');
            }
        });
    }

    /**
     * Suscribe un observer para cambios de tema
     */
    subscribe(callback) {
        this.observers.add(callback);
        return () => this.observers.delete(callback);
    }

    /**
     * Notifica a todos los observers sobre cambios de tema
     */
    notifyObservers(theme) {
        this.observers.forEach(callback => {
            try {
                callback(theme);
            } catch (error) {
                console.error('Error in theme observer:', error);
            }
        });
    }

    /**
     * Crea un botón de toggle de tema
     */
    createToggleButton(container, options = {}) {
        const {
            position = 'top-right',
            size = 'medium',
            showTooltip = true
        } = options;

        const button = document.createElement('button');
        button.id = options.id || 'theme-toggle';
        
        // Estilos base
        const baseClasses = 'theme-toggle-btn rounded-full transition-all duration-200 flex items-center justify-center';
        const sizeClasses = {
            small: 'p-1',
            medium: 'p-2', 
            large: 'p-3'
        };
        const positionClasses = {
            'top-left': 'absolute top-4 left-4',
            'top-right': 'absolute top-4 right-4',
            'bottom-left': 'absolute bottom-4 left-4',
            'bottom-right': 'absolute bottom-4 right-4'
        };

        button.className = `${baseClasses} ${sizeClasses[size]} ${positionClasses[position]}`;
        
        // Crear iconos
        this.createThemeIcons(button, size);
        
        // Event listener
        button.addEventListener('click', () => {
            this.handleToggleClick(button);
        });

        // Tooltip
        if (showTooltip) {
            button.title = 'Cambiar tema';
        }

        // Agregar al contenedor
        if (container) {
            container.appendChild(button);
        }

        return button;
    }

    /**
     * Crea los iconos de sol y luna
     */
    createThemeIcons(button, size) {
        const iconSize = size === 'large' ? '24' : size === 'small' ? '16' : '20';
        const iconClass = size === 'large' ? 'w-6 h-6' : size === 'small' ? 'w-4 h-4' : 'w-5 h-5';

        // Ícono de sol (visible en modo oscuro)
        const sunIcon = document.createElement('div');
        sunIcon.innerHTML = `
            <svg xmlns="http://www.w3.org/2000/svg" width="${iconSize}" height="${iconSize}" 
                 viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" 
                 stroke-linecap="round" stroke-linejoin="round" 
                 class="${iconClass} text-gray-300 transition-all duration-300 hidden dark:block">
                <circle cx="12" cy="12" r="5"/>
                <line x1="12" y1="1" x2="12" y2="3"/>
                <line x1="12" y1="21" x2="12" y2="23"/>
                <line x1="4.22" y1="4.22" x2="5.64" y2="5.64"/>
                <line x1="18.36" y1="18.36" x2="19.78" y2="19.78"/>
                <line x1="1" y1="12" x2="3" y2="12"/>
                <line x1="21" y1="12" x2="23" y2="12"/>
                <line x1="4.22" y1="19.78" x2="5.64" y2="18.36"/>
                <line x1="18.36" y1="5.64" x2="19.78" y2="4.22"/>
            </svg>
        `;

        // Ícono de luna (visible en modo claro)
        const moonIcon = document.createElement('div');
        moonIcon.innerHTML = `
            <svg xmlns="http://www.w3.org/2000/svg" width="${iconSize}" height="${iconSize}" 
                 viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" 
                 stroke-linecap="round" stroke-linejoin="round" 
                 class="${iconClass} text-gray-700 transition-all duration-300 block dark:hidden">
                <path d="M21 12.79A9 9 0 1 1 11.21 3 7 7 0 0 0 21 12.79z"/>
            </svg>
        `;

        button.appendChild(sunIcon);
        button.appendChild(moonIcon);
    }

    /**
     * Maneja el click en el botón de toggle
     */
    handleToggleClick(button) {
        // Feedback visual
        button.style.transform = 'scale(0.9)';
        
        setTimeout(() => {
            this.toggle();
            button.style.transform = '';
        }, 100);
    }
}

// Crear instancia global
window.themeManager = new ThemeManager();

// Exportar para uso como módulo
if (typeof module !== 'undefined' && module.exports) {
    module.exports = ThemeManager;
}

export default ThemeManager;