import { _loadModulesWithArguments, _loadModules } from "./modules/windows-manager/loads-scripts.js";
import { activeMenu, toggleSubMenu } from './modules/sidebar/SidebarScrtipt.js';
import { loading } from './modules/loading/loadingsScript.js';
import {generateMenuLinks, _openModule, _closeModule} from './modules/windows-manager/windows-manager.js'

window.lego = window.lego || {};

lego.loadModulesWithArguments = _loadModulesWithArguments;
lego.loadModules = _loadModules;
window.toggleSubMenu = toggleSubMenu;
window.lego.loading = loading;

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