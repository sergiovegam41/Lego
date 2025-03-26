import { _loadModulesWithArguments, _loadModules } from "./modules/windows-manager/loads-scripts.js";
import {generateMenuLinks, _openModule, _closeModule} from './modules/windows-manager/windows-manager.js'

window.lego = window.lego || {};

lego.loadModulesWithArguments = _loadModulesWithArguments;
lego.loadModules = _loadModules;


