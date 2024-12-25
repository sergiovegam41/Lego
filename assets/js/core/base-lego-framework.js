import { _loadModulesWithArguments, _loadModules } from "./modules/windows-manager/loads-scripts.js";
import { activeMenu, toggleSubMenu } from './modules/sidebar/SidebarScrtipt.js';
// import {_activeMenus, _openModule, _closeModule} from './modules/windows-manager/windows-manager.js'

window.lego = window.lego || {};

lego.loadModulesWithArguments = _loadModulesWithArguments;
lego.loadModules = _loadModules;
window.toggleSubMenu = toggleSubMenu;


activeMenu()
// _activeMenus()
