import { _loadModulesWithArguments, _loadModules } from "./modules/windows-manager/loads-scripts.js";
import { _openModule, _closeModule} from './modules/windows-manager/windows-manager.js'
import { loading } from './modules/loading/loadingsScript.js';

window.lego = window.lego || {};

lego.loadModulesWithArguments = _loadModulesWithArguments;
lego.loadModules = _loadModules;
lego.loading = loading;
