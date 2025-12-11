import { _loadModulesWithArguments, _loadModules } from "./modules/windows-manager/loads-scripts.js";
import { activeMenu, toggleSubMenu, MenuExpansionManager } from './modules/sidebar/SidebarScript.js';
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
window.MenuExpansionManager = MenuExpansionManager; // Exponer para uso global
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
}

// Initialize dynamic components system
if (!window.lego.components) {
  window.lego.components = new DynamicComponentsManager();
}

activeMenu()
generateMenuLinks()



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
