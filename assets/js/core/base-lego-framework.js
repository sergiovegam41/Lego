import { _loadModulesWithArguments, _loadModules } from "./modules/windows-manager/loads-scripts.js";
import { activeMenu, toggleSubMenu } from './modules/sidebar/SidebarScrtipt.js';
import { loading } from './modules/loading/loadingsScript.js';
import {generateMenuLinks, _openModule, _closeModule} from './modules/windows-manager/windows-manager.js'
import ThemeManager from './modules/theme/theme-manager.js';
import storageManager from './modules/storage/storage-manager.js';

window.lego = window.lego || {};

lego.loadModulesWithArguments = _loadModulesWithArguments;
lego.loadModules = _loadModules;
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