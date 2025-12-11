/**
 * ═══════════════════════════════════════════════════════════════════
 * LEGO FRAMEWORK - ThemeAwareComponent
 * ═══════════════════════════════════════════════════════════════════
 *
 * PROPÓSITO:
 * Clase base para componentes JS que necesitan reaccionar a cambios de tema.
 *
 * FILOSOFÍA:
 * - La mayoría de componentes NO necesitan esta clase (CSS variables es suficiente)
 * - Solo usar cuando se requiere lógica JS específica al tema
 * - Ejemplos: AG Grid, Canvas rendering, dynamic SVG, third-party libs
 *
 * USO:
 *
 * class MyTableComponent extends ThemeAwareComponent {
 *     constructor() {
 *         super();
 *         this.gridApi = null;
 *     }
 *
 *     onThemeChange(theme) {
 *         super.onThemeChange(theme);
 *         // Lógica específica de tema
 *         if (this.gridApi) {
 *             this.gridApi.setTheme(theme === 'dark' ? 'ag-theme-alpine-dark' : 'ag-theme-alpine');
 *         }
 *     }
 *
 *     destroy() {
 *         super.destroy();
 *         // Cleanup específico del componente
 *     }
 * }
 *
 * ═══════════════════════════════════════════════════════════════════
 */

class ThemeAwareComponent {
    /**
     * Constructor base
     * Se suscribe automáticamente al ThemeManager
     */
    constructor() {
        this._themeUnsubscribe = null;
        this._componentName = this.constructor.name;
        this._isDestroyed = false;

        this._autoSubscribeToTheme();

    }

    /**
     * Suscripción automática al ThemeManager
     * @private
     */
    _autoSubscribeToTheme() {
        if (typeof window === 'undefined') {
            console.warn(`[${this._componentName}] Window no disponible, no se puede suscribir al tema`);
            return;
        }

        // Esperar a que ThemeManager esté disponible
        this._waitForThemeManager().then(() => {
            if (this._isDestroyed) return;

            this._themeUnsubscribe = window.themeManager.subscribe((theme) => {
                this._handleThemeChange(theme);
            });

            // Llamar inmediatamente con tema actual
            const currentTheme = window.themeManager.getCurrentTheme();
            this._handleThemeChange(currentTheme);
        });
    }

    /**
     * Espera a que ThemeManager esté disponible
     * @private
     * @returns {Promise<void>}
     */
    _waitForThemeManager() {
        return new Promise((resolve) => {
            if (window.themeManager) {
                resolve();
                return;
            }

            // Polling cada 100ms hasta que esté disponible
            const checkInterval = setInterval(() => {
                if (window.themeManager || this._isDestroyed) {
                    clearInterval(checkInterval);
                    if (window.themeManager) {
                        resolve();
                    }
                }
            }, 100);

            // Timeout después de 5 segundos
            setTimeout(() => {
                clearInterval(checkInterval);
                if (!window.themeManager) {
                    console.warn(`[${this._componentName}] ThemeManager no disponible después de 5s`);
                }
            }, 5000);
        });
    }

    /**
     * Maneja cambios de tema internos
     * @private
     * @param {string} theme - 'light' o 'dark'
     */
    _handleThemeChange(theme) {
        if (this._isDestroyed) return;

        try {
            this.onThemeChange(theme);
        } catch (error) {
            console.error(`[${this._componentName}] Error en onThemeChange:`, error);
        }
    }

    /**
     * Callback cuando el tema cambia
     * Override en subclases para implementar lógica específica
     *
     * @param {string} theme - 'light' o 'dark'
     */
    onThemeChange(theme) {
    }

    /**
     * Obtiene el tema actual
     * @returns {string} 'light' o 'dark'
     */
    getCurrentTheme() {
        if (window.themeManager) {
            return window.themeManager.getCurrentTheme();
        }

        // Fallback: leer del DOM
        return document.documentElement.classList.contains('dark') ? 'dark' : 'light';
    }

    /**
     * Helper: Verifica si está en modo oscuro
     * @returns {boolean}
     */
    isDarkMode() {
        return this.getCurrentTheme() === 'dark';
    }

    /**
     * Helper: Verifica si está en modo claro
     * @returns {boolean}
     */
    isLightMode() {
        return this.getCurrentTheme() === 'light';
    }

    /**
     * Destruye el componente y limpia suscripciones
     * Override en subclases y llamar a super.destroy()
     */
    destroy() {
        this._isDestroyed = true;

        // Desuscribirse del ThemeManager
        if (this._themeUnsubscribe) {
            this._themeUnsubscribe();
            this._themeUnsubscribe = null;
        }

    }

    /**
     * Helper: Ejecuta callback solo en modo oscuro
     * @param {Function} callback
     */
    whenDark(callback) {
        if (this.isDarkMode()) {
            callback();
        }
    }

    /**
     * Helper: Ejecuta callback solo en modo claro
     * @param {Function} callback
     */
    whenLight(callback) {
        if (this.isLightMode()) {
            callback();
        }
    }

    /**
     * Helper: Obtiene valor según tema (pattern matching)
     * @param {any} lightValue - Valor para modo claro
     * @param {any} darkValue - Valor para modo oscuro
     * @returns {any}
     */
    themeValue(lightValue, darkValue) {
        return this.isLightMode() ? lightValue : darkValue;
    }
}

/**
 * ═══════════════════════════════════════════════════════════════════
 * EJEMPLO DE USO
 * ═══════════════════════════════════════════════════════════════════
 *
 * // Componente simple que solo necesita saber el tema
 * class ChartComponent extends ThemeAwareComponent {
 *     constructor(containerId) {
 *         super();
 *         this.container = document.getElementById(containerId);
 *         this.chart = null;
 *     }
 *
 *     onThemeChange(theme) {
 *         super.onThemeChange(theme);
 *
 *         const chartOptions = {
 *             backgroundColor: this.themeValue('#ffffff', '#1a1a1a'),
 *             textColor: this.themeValue('#000000', '#ffffff'),
 *         };
 *
 *         this.chart?.update(chartOptions);
 *     }
 *
 *     destroy() {
 *         this.chart?.destroy();
 *         super.destroy();
 *     }
 * }
 *
 * // Uso con helpers
 * class MapComponent extends ThemeAwareComponent {
 *     onThemeChange(theme) {
 *         this.whenDark(() => {
 *             this.map.setStyle('mapbox://styles/mapbox/dark-v10');
 *         });
 *
 *         this.whenLight(() => {
 *             this.map.setStyle('mapbox://styles/mapbox/light-v10');
 *         });
 *     }
 * }
 *
 * ═══════════════════════════════════════════════════════════════════
 */

// Exportar para uso como módulo
if (typeof module !== 'undefined' && module.exports) {
    module.exports = ThemeAwareComponent;
}

export default ThemeAwareComponent;
