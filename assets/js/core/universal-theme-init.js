/**
 * Universal Theme Initialization - Sistema universal para inicialización de tema
 * Este script se ejecuta de manera síncrona antes del renderizado para prevenir flash
 * Modo oscuro es el default natural para todo el framework
 */

(function() {
    'use strict';
    
    // Configuración universal
    const THEME_CONFIG = {
        STORAGE_KEY: 'lego_theme',
        DEFAULT_THEME: 'dark', // Dark mode como default natural
        THEMES: ['light', 'dark']
    };

    /**
     * Obtiene el tema que se debe aplicar
     * Prioridad: localStorage > preferencia sistema > default (dark)
     */
    function getThemeToApply() {
        try {
            // 1. Verificar localStorage primero
            const savedTheme = localStorage.getItem(THEME_CONFIG.STORAGE_KEY);
            if (savedTheme && THEME_CONFIG.THEMES.includes(savedTheme)) {
                return savedTheme;
            }

            // 2. Verificar preferencia del sistema si está disponible
            if (window.matchMedia) {
                const prefersDark = window.matchMedia('(prefers-color-scheme: dark)').matches;
                if (prefersDark) {
                    return 'dark';
                }
            }

            // 3. Default siempre a dark (naturaleza del framework)
            return THEME_CONFIG.DEFAULT_THEME;
        } catch (error) {
            console.warn('Error getting theme preference, using default:', error);
            return THEME_CONFIG.DEFAULT_THEME;
        }
    }

    /**
     * Aplica el tema inmediatamente para prevenir flash
     * LEGO Standard: Usar tanto html.dark como html.light
     */
    function applyThemeImmediately(theme) {
        try {
            const elements = [document.documentElement];
            
            // Agregar body si ya existe
            if (document.body) {
                elements.push(document.body);
            }

            elements.forEach(element => {
                if (theme === 'dark') {
                    element.classList.remove('light');
                    element.classList.add('dark');
                } else {
                    element.classList.remove('dark');
                    element.classList.add('light');
                }
            });

            // Establecer color-scheme para mejor soporte del navegador
            document.documentElement.style.colorScheme = theme;

            // Guardar en localStorage para consistencia
            localStorage.setItem(THEME_CONFIG.STORAGE_KEY, theme);
            
        } catch (error) {
            console.warn('Error applying theme immediately:', error);
        }
    }

    /**
     * Observer para cuando el body esté disponible
     * LEGO Standard: Usar tanto html.dark como html.light
     */
    function watchForBodyElement(theme) {
        if (document.body) {
            // Body ya existe, aplicar tema
            if (theme === 'dark') {
                document.body.classList.remove('light');
                document.body.classList.add('dark');
            } else {
                document.body.classList.remove('dark');
                document.body.classList.add('light');
            }
        } else {
            // Body no existe, observar hasta que aparezca
            const observer = new MutationObserver(function(mutations) {
                mutations.forEach(function(mutation) {
                    if (mutation.type === 'childList') {
                        const body = document.querySelector('body');
                        if (body) {
                            if (theme === 'dark') {
                                body.classList.remove('light');
                                body.classList.add('dark');
                            } else {
                                body.classList.remove('dark');
                                body.classList.add('light');
                            }
                            observer.disconnect();
                        }
                    }
                });
            });

            observer.observe(document.documentElement, {
                childList: true,
                subtree: true
            });
        }
    }

    /**
     * Inicialización principal
     */
    function initializeTheme() {
        const theme = getThemeToApply();
        applyThemeImmediately(theme);
        watchForBodyElement(theme);
        
        // Marcar que la inicialización universal ya ocurrió
        window.LEGO_THEME_INITIALIZED = true;
        window.LEGO_CURRENT_THEME = theme;
        
        return theme;
    }

    // Ejecutar inmediatamente si el documento ya está cargando/cargado
    if (document.readyState === 'loading') {
        // Ejecutar inmediatamente durante la carga
        initializeTheme();
    } else {
        // Documento ya está listo, ejecutar inmediatamente
        initializeTheme();
    }

    // Exponer funciones globales para uso del theme manager
    window.LEGO_THEME_UTILS = {
        getTheme: getThemeToApply,
        applyTheme: applyThemeImmediately,
        config: THEME_CONFIG
    };

})();