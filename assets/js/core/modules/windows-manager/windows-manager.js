
// export activeMenus;

class ModuleStore {
        
    constructor() {
    this.modules = {};
    this.activeModule = null;
    }
    _openModule(id, component) {
    if (!this.modules[id]) {
        this.modules[id] = { component, isActive: false };
    }
    Object.keys(this.modules).forEach(moduleId => {
        this.modules[moduleId].isActive = moduleId === id;
    });
    this.activeModule = id;
    }

    closeModule(id) {
    if (this.modules[id]) {
        delete this.modules[id];
    }
    if (this.activeModule === id) {
        const remainingModules = Object.keys(this.modules);
        this.activeModule = remainingModules.length > 0 ? remainingModules[0] : null;
        if (this.activeModule) {
        this.modules[this.activeModule].isActive = true;
        }
    }
    }

    getActiveModule() {
        return this.activeModule;
    }

    getModules() {
        return this.modules;
    }
}

const moduleStore = new ModuleStore();

function renderModule(id, content) {
    let container = document.getElementById(`module-${id}`);
    if (!container) {
    container = document.createElement('div');
    container.id = `module-${id}`;
    container.className = 'module-container';
    container.innerHTML = `<h2>Módulo ${id}</h2><p>${content}</p>`;
    document.getElementById('home-page').appendChild(container);
    }
    document.querySelectorAll('.module-container').forEach(module => module.classList.remove('active'));
    container.classList.add('active');
}

export function _closeModule(id) {
    const container = document.getElementById(`module-${id}`);
    if (container) {
    container.remove();
    }
    moduleStore.closeModule(id);
    updateMenu();
                     
    // Abrir el siguiente módulo en la lista si existe
    const nextActiveModule = moduleStore.getActiveModule();
    if (nextActiveModule) {
    renderModule(nextActiveModule, `Contenido dinámico del módulo ${nextActiveModule}`);
    }
}
export function _openModule(id) {
    moduleStore._openModule(id, {});
    renderModule(id, `Contenido dinámico del módulo ${id}`);
    updateMenu();
}

function updateMenu() {
    const activeModule = moduleStore.getActiveModule();
    const modules = moduleStore.getModules();

    document.querySelectorAll('.menu-item').forEach(item => {
    const id = item.dataset.moduleId;
    if (id === activeModule) {
        item.classList.add('active');
        item.classList.remove('inactive');
    } else if (modules[id]) {
        item.classList.add('inactive');
        item.classList.remove('active');
    } else {
        item.classList.remove('active', 'inactive');
    }
    });
}




export function generateMenuLinks(){

    console.log("generateMenuLinks")
    
    document.querySelectorAll('.menu_item_openable').forEach(item => {

        item.addEventListener('click', () => {

            const id = item.dataset.moduleId  || item.getAttribute("moduleId");
            if (moduleStore.getActiveModule() !== id) {
                _openModule(id);
            }

        });

    });
   
}
