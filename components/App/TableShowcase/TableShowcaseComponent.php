<?php
namespace Components\App\TableShowcase;

use Core\Attributes\ApiComponent;
use Core\Components\CoreComponent\CoreComponent;

use Components\Shared\Essentials\TableComponent\TableComponent;
use Components\Shared\Essentials\TableComponent\Dtos\ColumnDto;
use Components\Shared\Essentials\TableComponent\Collections\ColumnCollection;

/**
 * TableShowcaseComponent - Página de demostración del componente Table
 *
 * FILOSOFÍA LEGO:
 * Página completa que demuestra el TableComponent con AG Grid,
 * mostrando diferentes configuraciones y casos de uso.
 */
#[ApiComponent('/table-showcase', methods: ['GET'])]
class TableShowcaseComponent extends CoreComponent {

    protected $CSS_PATHS = ["./table-showcase.css"];
    protected $JS_PATHS = [];
    protected $JS_PATHS_WITH_ARG = [];

    public function __construct() {}

    protected function component(): string {
        // Cargar JavaScript del showcase
        $this->JS_PATHS_WITH_ARG[] = [
            new \Core\Dtos\ScriptCoreDTO("./table-showcase.js", [])
        ];

        // Ejemplo 1: Tabla básica de usuarios
        $usersTable = $this->renderUsersTable();

        // Ejemplo 2: Tabla con paginación y filtros
        $productsTable = $this->renderProductsTable();

        // Ejemplo 3: Tabla con selección múltiple
        $tasksTable = $this->renderTasksTable();

        // Ejemplo 4: Tabla con exportación
        $salesTable = $this->renderSalesTable();

        return <<<HTML
        <div class="table-showcase">
            <div class="table-showcase__header">
                <h1 class="table-showcase__title">Componente Table con AG Grid</h1>
                <p class="table-showcase__description">
                    Ejemplos de uso del TableComponent integrado con AG Grid, la biblioteca líder
                    para tablas de datos avanzadas en aplicaciones web.
                </p>
            </div>

            <div class="table-showcase__content">
                <!-- Tabla Básica -->
                <section class="table-showcase__section">
                    <h2 class="table-showcase__section-title">
                        <ion-icon name="people-outline"></ion-icon>
                        Tabla Básica - Usuarios
                    </h2>
                    <p class="table-showcase__section-description">
                        Tabla simple con columnas básicas, ordenamiento y dimensiones fijas.
                    </p>
                    <div class="table-showcase__example">
                        {$usersTable}
                    </div>
                </section>

                <!-- Tabla con Paginación -->
                <section class="table-showcase__section">
                    <h2 class="table-showcase__section-title">
                        <ion-icon name="cube-outline"></ion-icon>
                        Tabla con Paginación y Filtros - Productos
                    </h2>
                    <p class="table-showcase__section-description">
                        Tabla con paginación automática, filtros por columna y búsqueda.
                    </p>
                    <div class="table-showcase__example">
                        {$productsTable}
                    </div>
                </section>

                <!-- Tabla con Selección -->
                <section class="table-showcase__section">
                    <h2 class="table-showcase__section-title">
                        <ion-icon name="checkmark-done-outline"></ion-icon>
                        Tabla con Selección Múltiple - Tareas
                    </h2>
                    <p class="table-showcase__section-description">
                        Tabla con selección múltiple de filas y acciones sobre seleccionadas.
                    </p>
                    <div class="table-showcase__example">
                        {$tasksTable}
                    </div>
                    <div class="table-showcase__actions">
                        <button type="button" onclick="handleTasksSelection()" class="showcase-btn showcase-btn--primary">
                            <ion-icon name="create-outline"></ion-icon>
                            <span>Marcar seleccionadas como completadas</span>
                        </button>
                        <button type="button" onclick="clearTasksSelection()" class="showcase-btn showcase-btn--secondary">
                            <ion-icon name="close-outline"></ion-icon>
                            <span>Limpiar selección</span>
                        </button>
                    </div>
                </section>

                <!-- Tabla con Exportación -->
                <section class="table-showcase__section">
                    <h2 class="table-showcase__section-title">
                        <ion-icon name="analytics-outline"></ion-icon>
                        Tabla con Exportación - Ventas
                    </h2>
                    <p class="table-showcase__section-description">
                        Tabla con funciones de exportación a CSV/Excel y conteo de registros.
                    </p>
                    <div class="table-showcase__example">
                        {$salesTable}
                    </div>
                </section>

                <!-- Información Adicional -->
                <section class="table-showcase__section">
                    <h2 class="table-showcase__section-title">
                        <ion-icon name="information-circle-outline"></ion-icon>
                        Características de AG Grid
                    </h2>
                    <div class="table-showcase__features">
                        <div class="feature-card">
                            <ion-icon name="filter-outline"></ion-icon>
                            <h3>Filtros Avanzados</h3>
                            <p>Filtros por texto, número, fecha y set con operadores personalizables.</p>
                        </div>
                        <div class="feature-card">
                            <ion-icon name="swap-vertical-outline"></ion-icon>
                            <h3>Ordenamiento</h3>
                            <p>Ordenamiento multi-columna con indicadores visuales claros.</p>
                        </div>
                        <div class="feature-card">
                            <ion-icon name="documents-outline"></ion-icon>
                            <h3>Paginación</h3>
                            <p>Paginación configurable con selector de tamaño de página.</p>
                        </div>
                        <div class="feature-card">
                            <ion-icon name="resize-outline"></ion-icon>
                            <h3>Redimensionable</h3>
                            <p>Columnas redimensionables y reordenables por arrastre.</p>
                        </div>
                        <div class="feature-card">
                            <ion-icon name="download-outline"></ion-icon>
                            <h3>Exportación</h3>
                            <p>Exporta datos a CSV o Excel (Enterprise) con un click.</p>
                        </div>
                        <div class="feature-card">
                            <ion-icon name="color-palette-outline"></ion-icon>
                            <h3>Temas</h3>
                            <p>Múltiples temas pre-diseñados integrados con variables Lego.</p>
                        </div>
                    </div>
                </section>
            </div>
        </div>
        HTML;
    }

    /**
     * Tabla básica de usuarios
     */
    private function renderUsersTable(): string {
        $users = [
            ['id' => 1, 'name' => 'Juan Pérez', 'email' => 'juan@example.com', 'role' => 'Admin'],
            ['id' => 2, 'name' => 'María García', 'email' => 'maria@example.com', 'role' => 'Editor'],
            ['id' => 3, 'name' => 'Carlos López', 'email' => 'carlos@example.com', 'role' => 'Viewer'],
            ['id' => 4, 'name' => 'Ana Martínez', 'email' => 'ana@example.com', 'role' => 'Editor'],
            ['id' => 5, 'name' => 'Luis Rodríguez', 'email' => 'luis@example.com', 'role' => 'Admin'],
            ['id' => 6, 'name' => 'Sofia Torres', 'email' => 'sofia@example.com', 'role' => 'Viewer'],
            ['id' => 7, 'name' => 'Diego Ramírez', 'email' => 'diego@example.com', 'role' => 'Editor'],
        ];

        $columns = new ColumnCollection(
            new ColumnDto(field: "id", headerName: "ID", width: 80),
            new ColumnDto(field: "name", headerName: "Nombre", sortable: true, filter: true),
            new ColumnDto(field: "email", headerName: "Email", sortable: true, filter: true),
            new ColumnDto(field: "role", headerName: "Rol", sortable: true, filter: true, filterType: "set")
        );

        return (new TableComponent(
            id: "users-table",
            columns: $columns,
            rowData: $users,
            height: "400px"
        ))->render();
    }

    /**
     * Tabla de productos con paginación
     */
    private function renderProductsTable(): string {
        $products = [];
        for ($i = 1; $i <= 50; $i++) {
            $products[] = [
                'id' => $i,
                'name' => "Producto {$i}",
                'category' => ['Electrónica', 'Ropa', 'Hogar', 'Deportes'][($i - 1) % 4],
                'price' => rand(10, 1000),
                'stock' => rand(0, 100),
                'active' => $i % 3 !== 0 ? 'Sí' : 'No'
            ];
        }

        $columns = new ColumnCollection(
            new ColumnDto(field: "id", headerName: "ID", width: 80),
            new ColumnDto(field: "name", headerName: "Producto", sortable: true, filter: true, flex: true),
            new ColumnDto(field: "category", headerName: "Categoría", sortable: true, filter: true, filterType: "set"),
            new ColumnDto(field: "price", headerName: "Precio", sortable: true, filter: true, filterType: "number", width: 120),
            new ColumnDto(field: "stock", headerName: "Stock", sortable: true, filter: true, filterType: "number", width: 100),
            new ColumnDto(field: "active", headerName: "Activo", sortable: true, filter: true, filterType: "set", width: 100)
        );

        return (new TableComponent(
            id: "products-table",
            columns: $columns,
            rowData: $products,
            pagination: true,
            paginationPageSize: 10,
            paginationPageSizeSelector: [10, 20, 50],
            height: "500px"
        ))->render();
    }

    /**
     * Tabla de tareas con selección múltiple
     */
    private function renderTasksTable(): string {
        $tasks = [
            ['id' => 1, 'task' => 'Revisar documentación', 'assignee' => 'Juan Pérez', 'status' => 'Pendiente', 'priority' => 'Alta'],
            ['id' => 2, 'task' => 'Implementar feature X', 'assignee' => 'María García', 'status' => 'En progreso', 'priority' => 'Alta'],
            ['id' => 3, 'task' => 'Corregir bug #123', 'assignee' => 'Carlos López', 'status' => 'Pendiente', 'priority' => 'Media'],
            ['id' => 4, 'task' => 'Actualizar dependencias', 'assignee' => 'Ana Martínez', 'status' => 'Completada', 'priority' => 'Baja'],
            ['id' => 5, 'task' => 'Escribir tests unitarios', 'assignee' => 'Luis Rodríguez', 'status' => 'En progreso', 'priority' => 'Media'],
            ['id' => 6, 'task' => 'Revisar PRs pendientes', 'assignee' => 'Sofia Torres', 'status' => 'Pendiente', 'priority' => 'Alta'],
        ];

        $columns = new ColumnCollection(
            new ColumnDto(field: "id", headerName: "ID", width: 70, checkboxSelection: true, headerCheckboxSelection: true),
            new ColumnDto(field: "task", headerName: "Tarea", sortable: true, filter: true, flex: true),
            new ColumnDto(field: "assignee", headerName: "Asignado a", sortable: true, filter: true),
            new ColumnDto(field: "status", headerName: "Estado", sortable: true, filter: true, filterType: "set"),
            new ColumnDto(field: "priority", headerName: "Prioridad", sortable: true, filter: true, filterType: "set")
        );

        return (new TableComponent(
            id: "tasks-table",
            columns: $columns,
            rowData: $tasks,
            rowSelection: "multiple",
            height: "400px",
            onSelectionChanged: "onTasksSelectionChanged"
        ))->render();
    }

    /**
     * Tabla de ventas con exportación
     */
    private function renderSalesTable(): string {
        $sales = [];
        $months = ['Enero', 'Febrero', 'Marzo', 'Abril', 'Mayo', 'Junio'];
        $regions = ['Norte', 'Sur', 'Este', 'Oeste'];

        for ($i = 1; $i <= 30; $i++) {
            $sales[] = [
                'id' => $i,
                'month' => $months[($i - 1) % 6],
                'region' => $regions[($i - 1) % 4],
                'revenue' => rand(10000, 100000),
                'units' => rand(50, 500),
                'growth' => rand(-20, 50)
            ];
        }

        $columns = new ColumnCollection(
            new ColumnDto(field: "id", headerName: "ID", width: 70),
            new ColumnDto(field: "month", headerName: "Mes", sortable: true, filter: true, filterType: "set"),
            new ColumnDto(field: "region", headerName: "Región", sortable: true, filter: true, filterType: "set"),
            new ColumnDto(field: "revenue", headerName: "Ingresos ($)", sortable: true, filter: true, filterType: "number", width: 150),
            new ColumnDto(field: "units", headerName: "Unidades", sortable: true, filter: true, filterType: "number", width: 120),
            new ColumnDto(field: "growth", headerName: "Crecimiento (%)", sortable: true, filter: true, filterType: "number", width: 150)
        );

        return (new TableComponent(
            id: "sales-table",
            columns: $columns,
            rowData: $sales,
            pagination: true,
            paginationPageSize: 10,
            paginationPageSizeSelector: [10, 20, 50],
            enableExport: true,
            exportFileName: "ventas-reporte",
            height: "500px"
        ))->render();
    }
}
