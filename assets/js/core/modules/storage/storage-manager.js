/**
 * Storage Manager - Sistema unificado para manejo de localStorage
 * Centraliza todas las keys y operaciones de almacenamiento local
 */

// Constantes para las keys de localStorage
export const STORAGE_KEYS = {
  // Theme settings
  THEME: 'lego_theme',
  
  // UI Settings  
  SIDEBAR_WIDTH: 'lego_sidebar_width',
  SIDEBAR_COLLAPSED: 'lego_sidebar_collapsed',
  SIDEBAR_POSITION: 'lego_sidebar_position',
  
  // User preferences
  USER_PREFERENCES: 'lego_user_preferences',
  WINDOW_STATES: 'lego_window_states',
  
  // Session data
  ACCESS_TOKEN: 'access_token',
  REFRESH_TOKEN: 'refresh_token',
  EXPIRES_AT: 'expires_at',
  REFRESH_EXPIRES_AT: 'refresh_expires_at',
  
  // App state
  LAST_ROUTE: 'lego_last_route',
  ACTIVE_MODULES: 'lego_active_modules'
};

/**
 * Clase para manejo unificado del localStorage
 */
class StorageManager {
  constructor() {
    this.prefix = 'lego_';
    this.observers = new Map();
  }

  /**
   * Obtiene un valor del localStorage
   * @param {string} key - Key definida en STORAGE_KEYS
   * @param {*} defaultValue - Valor por defecto si no existe
   * @returns {*} Valor parseado o defaultValue
   */
  get(key, defaultValue = null) {
    try {
      const item = localStorage.getItem(key);
      if (item === null) return defaultValue;
      
      // Try to parse JSON, if fails return as string
      try {
        return JSON.parse(item);
      } catch {
        return item;
      }
    } catch (error) {
      console.warn(`Error reading localStorage key "${key}":`, error);
      return defaultValue;
    }
  }

  /**
   * Guarda un valor en localStorage
   * @param {string} key - Key definida en STORAGE_KEYS
   * @param {*} value - Valor a guardar
   * @returns {boolean} True si se guardó correctamente
   */
  set(key, value) {
    try {
      const serializedValue = typeof value === 'string' ? value : JSON.stringify(value);
      localStorage.setItem(key, serializedValue);
      
      // Notify observers
      this.notifyObservers(key, value);
      
      return true;
    } catch (error) {
      console.error(`Error saving to localStorage key "${key}":`, error);
      return false;
    }
  }

  /**
   * Elimina una key del localStorage
   * @param {string} key - Key a eliminar
   * @returns {boolean} True si se eliminó correctamente
   */
  remove(key) {
    try {
      localStorage.removeItem(key);
      this.notifyObservers(key, null);
      return true;
    } catch (error) {
      console.error(`Error removing localStorage key "${key}":`, error);
      return false;
    }
  }

  /**
   * Verifica si existe una key
   * @param {string} key - Key a verificar
   * @returns {boolean} True si existe
   */
  has(key) {
    return localStorage.getItem(key) !== null;
  }

  /**
   * Limpia todas las keys del framework (que empiecen con el prefix)
   */
  clear() {
    try {
      const keysToRemove = [];
      for (let i = 0; i < localStorage.length; i++) {
        const key = localStorage.key(i);
        if (key && key.startsWith(this.prefix)) {
          keysToRemove.push(key);
        }
      }
      
      keysToRemove.forEach(key => localStorage.removeItem(key));
      
      // Notify observers
      keysToRemove.forEach(key => this.notifyObservers(key, null));
      
      return true;
    } catch (error) {
      console.error('Error clearing localStorage:', error);
      return false;
    }
  }

  /**
   * Obtiene todas las keys relacionadas al framework
   * @returns {Object} Objeto con todas las keys y valores
   */
  getAll() {
    const data = {};
    try {
      for (let i = 0; i < localStorage.length; i++) {
        const key = localStorage.key(i);
        if (key && key.startsWith(this.prefix)) {
          data[key] = this.get(key);
        }
      }
      return data;
    } catch (error) {
      console.error('Error getting all localStorage data:', error);
      return {};
    }
  }

  /**
   * Suscribe un observer para cambios en una key específica
   * @param {string} key - Key a observar
   * @param {Function} callback - Función callback
   * @returns {Function} Función para desuscribir
   */
  subscribe(key, callback) {
    if (!this.observers.has(key)) {
      this.observers.set(key, new Set());
    }
    
    this.observers.get(key).add(callback);
    
    // Return unsubscribe function
    return () => {
      const keyObservers = this.observers.get(key);
      if (keyObservers) {
        keyObservers.delete(callback);
        if (keyObservers.size === 0) {
          this.observers.delete(key);
        }
      }
    };
  }

  /**
   * Notifica a los observers sobre cambios
   * @param {string} key - Key que cambió
   * @param {*} value - Nuevo valor
   */
  notifyObservers(key, value) {
    const keyObservers = this.observers.get(key);
    if (keyObservers) {
      keyObservers.forEach(callback => {
        try {
          callback(value, key);
        } catch (error) {
          console.error('Error in storage observer:', error);
        }
      });
    }
  }

  /**
   * Métodos de conveniencia para settings específicos
   */

  // Theme
  getTheme() {
    return this.get(STORAGE_KEYS.THEME, 'light');
  }

  setTheme(theme) {
    return this.set(STORAGE_KEYS.THEME, theme);
  }

  // Sidebar
  getSidebarWidth() {
    return this.get(STORAGE_KEYS.SIDEBAR_WIDTH, 240); // Default 240px
  }

  setSidebarWidth(width) {
    return this.set(STORAGE_KEYS.SIDEBAR_WIDTH, width);
  }

  getSidebarCollapsed() {
    return this.get(STORAGE_KEYS.SIDEBAR_COLLAPSED, false);
  }

  setSidebarCollapsed(collapsed) {
    return this.set(STORAGE_KEYS.SIDEBAR_COLLAPSED, collapsed);
  }

  // User preferences
  getUserPreferences() {
    return this.get(STORAGE_KEYS.USER_PREFERENCES, {});
  }

  setUserPreferences(preferences) {
    return this.set(STORAGE_KEYS.USER_PREFERENCES, preferences);
  }

  updateUserPreference(key, value) {
    const preferences = this.getUserPreferences();
    preferences[key] = value;
    return this.setUserPreferences(preferences);
  }

  // Session tokens
  getAccessToken() {
    return this.get(STORAGE_KEYS.ACCESS_TOKEN);
  }

  setAccessToken(token) {
    return this.set(STORAGE_KEYS.ACCESS_TOKEN, token);
  }

  getRefreshToken() {
    return this.get(STORAGE_KEYS.REFRESH_TOKEN);
  }

  setRefreshToken(token) {
    return this.set(STORAGE_KEYS.REFRESH_TOKEN, token);
  }

  // Session management
  setSession(tokens) {
    const { access_token, refresh_token, expires_at, refresh_expires_at } = tokens;
    
    this.set(STORAGE_KEYS.ACCESS_TOKEN, access_token);
    this.set(STORAGE_KEYS.REFRESH_TOKEN, refresh_token);
    this.set(STORAGE_KEYS.EXPIRES_AT, expires_at);
    this.set(STORAGE_KEYS.REFRESH_EXPIRES_AT, refresh_expires_at);
  }

  clearSession() {
    this.remove(STORAGE_KEYS.ACCESS_TOKEN);
    this.remove(STORAGE_KEYS.REFRESH_TOKEN);
    this.remove(STORAGE_KEYS.EXPIRES_AT);
    this.remove(STORAGE_KEYS.REFRESH_EXPIRES_AT);
  }
}

// Create global instance
const storageManager = new StorageManager();

// Export both the class and instance
export default storageManager;
export { StorageManager };