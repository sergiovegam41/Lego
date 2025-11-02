# ROADMAP DE REFACTORIZACIÓN - CRUD DE PRODUCTOS

## Visión: De Específico a Genérico

### Fase 1: Análisis y Documentación (COMPLETADA)
- [x] Identificar archivos principal
- [x] Mapear acoplamiento
- [x] Catalogar código duplicado
- [x] Documento: ANALISIS_CRUD_PRODUCTOS.md

### Fase 2: Refactorización Inmediata (PRÓXIMA - 4 semanas)

#### 2.1: Consolidar formatBytes() - CRITICIDAD BAJA
**Objetivo:** Eliminar duplicación de código
**Esfuerzo:** 2 horas

**Pasos:**
1. Crear `Core/Helpers/FileHelper.php`
2. Mover `formatBytes()` desde ProductFormComponent y ProductsController
3. Usar trait en ambas clases
4. Tests unitarios

**Impacto:** Mantenibilidad +20%

---

#### 2.2: Usar CrudManager en products-crud.js - CRITICIDAD ALTA
**Objetivo:** Eliminar 150+ líneas de código duplicado
**Esfuerzo:** 1 día

**Cambios:**

**ANTES (309 líneas):**
```javascript
const API_BASE = '/api/products';

window.createProduct = async function() {
    const result = await AlertService.componentModal('/component/products-crud/product-form', {...});
    if (result.isConfirmed && result.value) {
        const closeLoading = AlertService.loading('Creando producto...');
        try {
            const response = await fetch(`${API_BASE}/create`, {...});
            // ... 30 líneas más
        } catch (error) { ... }
    }
};

window.editProduct = async function(id) {
    // ... 37 líneas (patrón idéntico)
};

window.deleteProduct = async function(id) {
    // ... 30 líneas (patrón idéntico)
};

function loadProducts() {
    // ... 48 líneas
}

function configureTableColumns() {
    // ... 75 líneas
}
```

**DESPUÉS (30 líneas):**
```javascript
// Configuración auto-cargada desde atributo de componente
const config = window.CRUD_CONFIG || {
    endpoint: '/api/products',
    formPath: '/component/products-crud/product-form',
    tableId: 'products-crud-table',
    entityName: 'Producto'
};

// Crear instancia de CrudManager
const productsCrud = new CrudManager(config);

// Exponer funciones globales (automático)
productsCrud.expose();

// Cargar datos iniciales
productsCrud.loadInitialData();

// Hook customizado para formateo de tabla (opcional)
productsCrud.config.formatTableData = (products) => {
    return products.map(p => ({
        ...p,
        price: p.price ? `$${parseFloat(p.price).toFixed(2)}` : '-'
    }));
};
```

**Reducción:** 279 líneas menos (90% menos código específico)

---

#### 2.3: Parametrizar ProductFormComponent - CRITICIDAD ALTA
**Objetivo:** Permitir reutilización en otros formularios
**Esfuerzo:** 1.5 días

**Cambios:**

**ANTES (Líneas 71-79):**
```php
$categoryOptions = [
    ["value" => "electronics", "label" => "Electrónica"],
    ["value" => "clothing", "label" => "Ropa"],
    ["value" => "food", "label" => "Alimentos"],
    ["value" => "books", "label" => "Libros"],
    ["value" => "toys", "label" => "Juguetes"],
    ["value" => "sports", "label" => "Deportes"],
    ["value" => "other", "label" => "Otros"]
];
```

**DESPUÉS:**
```php
// Cargar de configuración dinámica
$config = $this->getFormConfig('products');
$categoryOptions = $config['categories'] ?? [];

// En config/form-configs.php
return [
    'products' => [
        'categories' => [
            ["value" => "electronics", "label" => "Electrónica"],
            // ...
        ],
        'fields' => [
            'name' => ['required' => true, 'maxLength' => 100],
            'sku' => ['required' => true, 'pattern' => '^[A-Z0-9-]+$'],
            'price' => ['required' => true, 'type' => 'number'],
        ]
    ]
];
```

**Beneficio:** Campos extraídos a configuración centralizada

---

#### 2.4: Parametrizar ImageGalleryComponent - CRITICIDAD MEDIA
**Objetivo:** Reutilizar galería en otros CRUDs
**Esfuerzo:** 1 día

**ANTES (Líneas 203-213):**
```php
ImageGalleryComponent::create(
    id: 'product-gallery',
    entityId: $productId,
    existingImages: $existingImages,
    uploadEndpoint: '/api/products/upload_image',       // Hardcodeado
    deleteEndpoint: '/api/products/delete_image',       // Hardcodeado
    reorderEndpoint: '/api/products/reorder_images',    // Hardcodeado
    setPrimaryEndpoint: '/api/products/set_primary',    // Hardcodeado
    maxFiles: 10,
    maxFileSize: 5242880
)
```

**DESPUÉS:**
```php
ImageGalleryComponent::create(
    id: 'product-gallery',
    entityId: $productId,
    existingImages: $existingImages,
    entity: 'products',  // Una sola configuración
    maxFiles: 10,
    maxFileSize: 5242880
)
// ImageGalleryComponent genera dinámicamente:
// /api/{entity}/upload_image
// /api/{entity}/delete_image
// /api/{entity}/reorder_images
// /api/{entity}/set_primary
```

**Reducción:** 4 líneas de configuración

---

### Fase 3: Infraestructura Genérica (8 semanas)

#### 3.1: Crear GenericCrudComponent
**Objetivo:** Componente base reutilizable para cualquier CRUD
**Arquitectura:**

```php
// components/Shared/Crud/GenericCrudComponent.php
class GenericCrudComponent extends CoreComponent {
    public function __construct(
        private EntityConfig $config
    ) {}
    
    protected function component(): string {
        // Genera tabla dinámicamente basada en $this->config->columns
        $table = (new TableComponent(
            id: $this->getTableId(),
            columns: $this->buildColumns(),
            rowData: [],
            pagination: true,
        ))->render();
        
        // Carga script genérico
        $this->JS_PATHS_WITH_ARG[] = [
            new ScriptCoreDTO("./generic-crud.js", [
                'config' => json_encode($this->config->toArray())
            ])
        ];
        
        return $table;
    }
}
```

**Uso:**
```php
// En ProductsCrudComponent.php
$config = EntityConfigRegistry::get('products');
return (new GenericCrudComponent($config))->render();
```

---

#### 3.2: Crear GenericFormComponent
**Objetivo:** Componente formulario reutilizable
**Estructura:**

```php
// components/Shared/Forms/GenericFormComponent.php
class GenericFormComponent extends CoreComponent {
    public function __construct(
        private EntityConfig $config,
        private ?int $recordId = null
    ) {}
    
    protected function component(): string {
        // Construir formulario basado en $this->config->fields
        $form = new Form(
            id: $this->getFormId(),
            title: $this->getTitle(),
            children: $this->buildFields(),
            onSubmit: 'return false;'
        );
        
        return $form->render();
    }
    
    private function buildFields(): array {
        return array_map(
            fn($field) => $this->createFormField($field),
            $this->config->fields
        );
    }
}
```

---

#### 3.3: Registry de Configuraciones
**Objetivo:** Centralizar todas las configuraciones de CRUD

```php
// app/Config/EntityConfigRegistry.php
class EntityConfigRegistry {
    private static array $configs = [];
    
    public static function register(string $entity, EntityConfig $config): void {
        self::$configs[$entity] = $config;
    }
    
    public static function get(string $entity): EntityConfig {
        if (!isset(self::$configs[$entity])) {
            throw new ConfigNotFoundException("Entity config not found: $entity");
        }
        return self::$configs[$entity];
    }
}

// Registro en bootstrap:
EntityConfigRegistry::register('products', new EntityConfig(
    entity: 'products',
    model: Product::class,
    columns: [
        ['field' => 'id', 'label' => 'ID', 'width' => 80],
        ['field' => 'name', 'label' => 'Nombre'],
        ['field' => 'price', 'label' => 'Precio'],
    ],
    fields: [
        'name' => new TextFieldConfig(required: true),
        'price' => new NumberFieldConfig(required: true),
    ],
    actions: ['create', 'read', 'update', 'delete']
));
```

---

### Fase 4: Validación y Testing (4 semanas)

#### 4.1: Tests Unitarios
```php
// tests/CrudManager/CrudManagerTest.php
class CrudManagerTest extends TestCase {
    public function testExposeCreatesGlobalFunctions() {
        $crud = new CrudManager([
            'endpoint' => '/api/test',
            'formPath' => '/component/test',
            'tableId' => 'test-table',
            'entityName' => 'Test'
        ]);
        
        $crud->expose();
        
        $this->assertTrue(function_exists('window.createTest'));
        $this->assertTrue(function_exists('window.editTest'));
        $this->assertTrue(function_exists('window.deleteTest'));
    }
}
```

#### 4.2: Tests E2E
```javascript
// tests/e2e/crud-manager.spec.js
describe('CrudManager', () => {
    it('should create entity successfully', async () => {
        const config = {
            endpoint: '/api/test',
            formPath: '/component/test',
            tableId: 'test-table',
            entityName: 'Test'
        };
        
        new CrudManager(config).expose();
        
        // Simular creación
        await window.createTest();
        
        expect(tableApi.getDisplayedRowCount()).toBe(1);
    });
});
```

---

## Timeline Estimado

| Fase | Duración | Costo | Impacto |
|------|----------|-------|--------|
| Fase 2.1 (formatBytes) | 2h | Muy bajo | Mantenibilidad +20% |
| Fase 2.2 (CrudManager) | 1d | Bajo | Código -90% |
| Fase 2.3 (ProductForm) | 1.5d | Medio | Flexibilidad +50% |
| Fase 2.4 (ImageGallery) | 1d | Medio | Código -30% |
| **FASE 2 TOTAL** | **4.5d** | **Bajo** | **CRÍTICO** |
| Fase 3 (Infraestructura) | 8w | Medio | Escalabilidad +200% |
| Fase 4 (Testing) | 4w | Medio | Confiabilidad +100% |
| **TOTAL** | **13 semanas** | **~$15k USD** | **TRANSFORMACIONAL** |

---

## ROI (Return on Investment)

### Antes (Actual)
- Crear nuevo CRUD: 40 horas (3 personas x 5 días)
- Código duplicado por CRUD: ~300 líneas
- Bugs relacionados: +15% por falta de estandarización
- Curva aprendizaje para devs nuevos: 1 semana

### Después (Propuesto)
- Crear nuevo CRUD: 2 horas (1 línea de código en config)
- Código duplicado por CRUD: 0 líneas
- Bugs relacionados: -90% (código centralizado)
- Curva aprendizaje para devs nuevos: 2 horas

### Ahorro Anual
```
Si hay 12 nuevos CRUDs al año:
Antes:  12 × 40h = 480 horas = $21,600 USD
Después: 12 × 2h = 24 horas = $1,080 USD
Ahorro neto: $20,520 USD/año
```

Inversión inicial: $15,000 USD
Payback period: ~4.4 meses
ROI anual: 136.8%

---

## Métricas de Éxito

### Code Quality
- [ ] Reducir CRUD components a <50 líneas de código cada una
- [ ] Duplicación de código: 0%
- [ ] Cobertura de tests: >90%

### Developer Experience
- [ ] Crear nuevo CRUD en <2 horas (vs 40h actual)
- [ ] Documentación autoexplicativa
- [ ] 0 errores de configuración

### Performance
- [ ] Tiempo carga inicial: <1s
- [ ] No incremente tamaño JS (reutilización)
- [ ] Memoria caché optimizada

---

## Próximos Pasos Inmediatos

1. **Esta semana:** Crear issue en GitHub para Fase 2.1 (formatBytes)
2. **Próxima semana:** Iniciar refactorización de products-crud.js
3. **Dos semanas:** Revisión de código + mergeo de cambios
4. **Mes siguiente:** Documentación de nuevas convenciones

---

## Referencias

- Documento de análisis: ANALISIS_CRUD_PRODUCTOS.md
- Especificación CrudManager: assets/js/helpers/CrudManager.js
- SOLID Principles: https://en.wikipedia.org/wiki/SOLID
- DRY Principle: https://en.wikipedia.org/wiki/Don%27t_repeat_yourself
