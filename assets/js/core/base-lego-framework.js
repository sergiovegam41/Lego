import { _loadModulesWithArguments, _loadModules } from "./modules/loads-scripts.js";
import { ready } from './sidebar/SidebarScrtipt.js';


window.lego = window.lego || {};

lego.loadModulesWithArguments = _loadModulesWithArguments;
lego.loadModules = _loadModules;


ready()