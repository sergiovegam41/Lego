import { _loadModulesWithArguments, _loadModules } from "./modules/windows-manager/loads-scripts.js";
import { activeMenu, toggleSubMenu } from './modules/sidebar/SidebarScript.js';
import { loading } from './modules/loading/loadingsScript.js';
import {generateMenuLinks, _openModule, _closeModule} from './modules/windows-manager/windows-manager.js'
import ThemeManager from './modules/theme/theme-manager.js';
import storageManager from './modules/storage/storage-manager.js';
import DynamicComponentsManager from './modules/components/dynamic-components-manager.js';

window.lego = window.lego || {};

lego.loadModulesWithArguments = _loadModulesWithArguments;
lego.loadModules = _loadModules;
lego.openModule = _openModule;
lego.closeModule = _closeModule;
window.toggleSubMenu = toggleSubMenu;
window.lego.loading = loading;

// Initialize unified storage system
if (!window.storageManager) {
  window.storageManager = storageManager;
}

// Initialize unified theme system
if (!window.themeManager) {
  window.themeManager = new ThemeManager();
}

// Initialize unified events system
// IMPORTANTE: lego-events.js debe cargarse ANTES de este archivo
// y expone window.legoEvents directamente (sin ES6 modules)
if (!window.lego.events && window.legoEvents) {
  window.lego.events = window.legoEvents;
  console.log('[Lego] Sistema de eventos disponible en window.lego.events');
}

// Initialize dynamic components system
if (!window.lego.components) {
  window.lego.components = new DynamicComponentsManager();
  console.log('[Lego] Sistema de componentes dinámicos disponible en window.lego.components');
}

activeMenu()
generateMenuLinks()

// Auto-load dashboard on /admin page if no module is active
setTimeout(() => {
  console.log('[Lego] Checking for auto-load dashboard...');
  console.log('[Lego] Current path:', window.location.pathname);
  console.log('[Lego] moduleStore:', window.moduleStore);
  console.log('[Lego] activeModule:', window.moduleStore?.getActiveModule());

  // Only auto-load on /admin route and if no module is active
  if (window.location.pathname === '/admin' &&
      window.moduleStore &&
      !window.moduleStore.getActiveModule() &&
      window.openModule) {
    console.log('[Lego] Auto-loading inicio module...');
    const HOST_NAME = window.location.origin;
    window.openModule('inicio', `${HOST_NAME}/component/inicio`, 'Inicio', {
      url: `${HOST_NAME}/component/inicio`,
      name: 'Inicio'
    });
  } else {
    console.log('[Lego] Skipping auto-load');
  }
}, 500); // Wait 500ms to ensure everything is initialized


/*
loading(true,{
    withMenu:true
});

loading(false,{
    success:true,
    message:"ok"
});
*/


// _activeMenus()
