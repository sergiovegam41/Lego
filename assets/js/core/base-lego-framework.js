import { _loadModulesWithArguments, _loadModules } from "./modules/windows-manager/loads-scripts.js";
import { activeMenu, toggleSubMenu } from './modules/sidebar/SidebarScrtipt.js';
import { loading } from './modules/loading/loadingsScript.js';
// import {_activeMenus, _openModule, _closeModule} from './modules/windows-manager/windows-manager.js'

window.lego = window.lego || {};

lego.loadModulesWithArguments = _loadModulesWithArguments;
lego.loadModules = _loadModules;
window.toggleSubMenu = toggleSubMenu;

// lego.openModule = _openModule;
// lego.closeModule = _closeModule;
// lego.activeMenus = _activeMenus;

activeMenu()




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