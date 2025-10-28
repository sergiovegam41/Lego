/**
 * TableShowcase JavaScript
 *
 * FILOSOFÍA LEGO:
 * Funciones de demostración para interactuar con las tablas.
 */

let context = {CONTEXT};

// Callback cuando cambia la selección de tareas
window.onTasksSelectionChanged = function(event) {
    const selectedRows = event.api.getSelectedRows();
    console.log('[Table Showcase] Tareas seleccionadas:', selectedRows);
};

// Manejar selección de tareas (tasks-table se sanitiza a tasks_table)
window.handleTasksSelection = function() {
    if (typeof legoTable_tasks_table_getSelectedRows === 'undefined') {
        console.error('[Table Showcase] La tabla de tareas aún no está inicializada');
        alert('Por favor, espera a que la tabla termine de cargar.');
        return;
    }

    const selectedRows = legoTable_tasks_table_getSelectedRows();

    if (selectedRows.length === 0) {
        alert('Por favor, selecciona al menos una tarea.');
        return;
    }

    const taskNames = selectedRows.map(row => row.task).join(', ');
    alert(`Marcando como completadas:\n\n${taskNames}\n\n(${selectedRows.length} tarea(s) seleccionada(s))`);

    console.log('[Table Showcase] Tareas a completar:', selectedRows);
};

// Limpiar selección de tareas
window.clearTasksSelection = function() {
    if (typeof legoTable_tasks_table_deselectAll === 'undefined') {
        console.error('[Table Showcase] La tabla de tareas aún no está inicializada');
        alert('Por favor, espera a que la tabla termine de cargar.');
        return;
    }

    legoTable_tasks_table_deselectAll();
    console.log('[Table Showcase] Selección limpiada');
};

console.log('[Table Showcase] Página de demostración cargada');
