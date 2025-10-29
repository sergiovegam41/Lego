# Sistema de Gestión de Imágenes para Productos

## Descripción

Sistema completo para gestionar múltiples imágenes por producto con las siguientes características:

- ✅ Carga múltiple de imágenes (drag & drop)
- ✅ Preview y gestión visual de imágenes
- ✅ Marcado de imagen principal
- ✅ Reordenamiento por drag & drop
- ✅ Almacenamiento en MinIO
- ✅ Eliminación de imágenes (BD + MinIO)
- ✅ Validaciones (tipo, tamaño, cantidad)
- ✅ Theme-aware (dark/light mode)

## Arquitectura

### Base de Datos

**Tabla `product_images`:**
- `id`: Primary key
- `product_id`: FK a products (cascade on delete)
- `url`: URL pública de MinIO
- `key`: Key/path en MinIO para eliminación
- `original_name`: Nombre original del archivo
- `size`: Tamaño en bytes
- `mime_type`: Tipo MIME
- `order`: Orden de visualización
- `is_primary`: Marca si es la imagen principal
- `created_at`, `updated_at`: Timestamps

### Modelos Eloquent

#### ProductImage.php
```php
use App\Models\ProductImage;

// Obtener todas las imágenes de un producto
$images = ProductImage::where('product_id', $productId)->ordered()->get();

// Obtener solo la imagen principal
$primary = ProductImage::where('product_id', $productId)->primary()->first();

// Crear nueva imagen
$image = ProductImage::create([
    'product_id' => $productId,
    'url' => 'http://minio.local/products/images/abc.jpg',
    'key' => 'products/images/abc.jpg',
    'original_name' => 'producto.jpg',
    'size' => 125634,
    'mime_type' => 'image/jpeg',
    'order' => 0,
    'is_primary' => true
]);

// Accessors disponibles
$image->size_formatted; // "122.69 KB"
$image->extension;      // "jpg"
```

#### Product.php (actualizado)
```php
use App\Models\Product;

// Obtener producto con sus imágenes
$product = Product::with('images')->find($id);

// Obtener todas las imágenes (relación)
$images = $product->images;

// Obtener solo la imagen principal
$primaryImage = $product->primaryImage;

// Accessor para URL de imagen principal
$url = $product->primary_image_url; // Intenta relación, fallback a image_url legacy
```

## API Endpoints

### 1. Upload Image
```
POST /api/products/upload_image
Content-Type: multipart/form-data

Campos:
- image: File (required) - Archivo de imagen
- entity_id: Integer (optional) - ID del producto

Response:
{
  "success": true,
  "message": "Imagen subida correctamente",
  "data": {
    "id": 123,
    "product_id": 45,
    "url": "http://minio.local/products/images/product_abc.jpg",
    "key": "products/images/product_abc.jpg",
    "original_name": "mi-producto.jpg",
    "size": 125634,
    "size_formatted": "122.69 KB",
    "mime_type": "image/jpeg",
    "order": 0,
    "is_primary": true
  }
}
```

### 2. Delete Image
```
POST /api/products/delete_image
Content-Type: application/json

Body:
{
  "id": 123
}

Response:
{
  "success": true,
  "message": "Imagen eliminada correctamente",
  "data": null
}
```

### 3. Reorder Images
```
POST /api/products/reorder_images
Content-Type: application/json

Body:
{
  "images": [
    { "id": 123, "order": 0 },
    { "id": 124, "order": 1 },
    { "id": 125, "order": 2 }
  ]
}

Response:
{
  "success": true,
  "message": "Orden actualizado correctamente",
  "data": null
}
```

### 4. Set Primary Image
```
POST /api/products/set_primary
Content-Type: application/json

Body:
{
  "image_id": 123
}

Response:
{
  "success": true,
  "message": "Imagen principal actualizada",
  "data": null
}
```

## Componente ImageGalleryComponent

### Uso Básico

```php
use Components\Shared\Essentials\ImageGalleryComponent\ImageGalleryComponent;

// En tu formulario de edición de producto
$gallery = ImageGalleryComponent::create(
    id: 'product-gallery',
    entityId: $product->id,
    existingImages: $product->images->toArray(),
    uploadEndpoint: '/api/products/upload_image',
    deleteEndpoint: '/api/products/delete_image',
    reorderEndpoint: '/api/products/reorder_images',
    setPrimaryEndpoint: '/api/products/set_primary',
    maxFiles: 10,
    maxFileSize: 5242880 // 5MB en bytes
);

echo $gallery;
```

### Parámetros del Componente

| Parámetro | Tipo | Default | Descripción |
|-----------|------|---------|-------------|
| `id` | string | - | ID único del componente (required) |
| `entityId` | int\|null | null | ID del producto (null para nuevo) |
| `existingImages` | array | [] | Array de imágenes existentes |
| `uploadEndpoint` | string | '' | Endpoint para upload |
| `deleteEndpoint` | string | '' | Endpoint para delete |
| `reorderEndpoint` | string | '' | Endpoint para reorder |
| `setPrimaryEndpoint` | string | '' | Endpoint para set primary |
| `maxFiles` | int | 10 | Cantidad máxima de imágenes |
| `maxFileSize` | int | 5242880 | Tamaño máximo por archivo (bytes) |
| `acceptedTypes` | array | ['image/jpeg', 'image/png', 'image/webp', 'image/gif'] | Tipos MIME permitidos |
| `height` | string | '400px' | Altura del componente |

### Formato de `existingImages`

```php
[
    [
        'id' => 123,
        'url' => 'http://minio.local/products/images/abc.jpg',
        'original_name' => 'producto.jpg',
        'size' => 125634,
        'size_formatted' => '122.69 KB',
        'is_primary' => true,
        'order' => 0
    ],
    // ... más imágenes
]
```

## Integración con MinIO

El sistema utiliza `StorageService` para interactuar con MinIO:

```php
use Core\Services\Storage\StorageService;

$storage = StorageService::getInstance();

// Subir archivo
$result = $storage->upload(
    $key = 'products/images/abc.jpg',
    $contents = file_get_contents($tmpFile),
    $contentType = 'image/jpeg'
);

// $result = [
//     'success' => true,
//     'url' => 'http://minio.local/products/images/abc.jpg',
//     'key' => 'products/images/abc.jpg'
// ]

// Eliminar archivo
$storage->delete($key = 'products/images/abc.jpg');
```

## Validaciones

### Frontend (JavaScript)
- Tipo de archivo (mime type)
- Tamaño máximo por archivo
- Cantidad máxima de archivos

### Backend (PHP)
- Validación de `$_FILES['image']`
- Validación de mime type con `finfo`
- Validación de tamaño
- Validación de existencia del producto
- Validación de extensión

## Flujo de Trabajo

### 1. Crear Producto Nuevo (sin imágenes)

```php
// Paso 1: Mostrar formulario de creación
$gallery = ImageGalleryComponent::create(
    id: 'product-gallery-new',
    entityId: null, // Nuevo producto, sin ID aún
    uploadEndpoint: '/api/products/upload_image',
    deleteEndpoint: '/api/products/delete_image'
);

// Paso 2: Usuario crea el producto (sin imágenes)
// Las imágenes las puede agregar después editando

// Paso 3: O guardar las URLs temporales y asociar después
```

### 2. Editar Producto Existente (con imágenes)

```php
// Paso 1: Cargar producto con imágenes
$product = Product::with('images')->find($id);

// Paso 2: Mostrar galería con imágenes existentes
$gallery = ImageGalleryComponent::create(
    id: 'product-gallery-' . $product->id,
    entityId: $product->id,
    existingImages: $product->images->map(function($img) {
        return [
            'id' => $img->id,
            'url' => $img->url,
            'original_name' => $img->original_name,
            'size' => $img->size,
            'size_formatted' => $img->size_formatted,
            'is_primary' => $img->is_primary,
            'order' => $img->order
        ];
    })->toArray(),
    uploadEndpoint: '/api/products/upload_image',
    deleteEndpoint: '/api/products/delete_image',
    reorderEndpoint: '/api/products/reorder_images',
    setPrimaryEndpoint: '/api/products/set_primary'
);
```

### 3. Mostrar Imágenes en Tabla (AG Grid)

```php
use Components\Shared\Essentials\TableComponent\Renderers\ImageRenderer;

new ColumnDto(
    field: "primary_image_url",
    headerName: "Imagen",
    cellRenderer: ImageRenderer::create(
        size: 'small',
        shape: 'rounded',
        showPreview: true
    )
)
```

## Funcionalidades del Componente

### Drag & Drop
- Arrastra archivos desde el explorador a la zona de drop
- Visual feedback durante el drag
- Validación automática

### Upload con Progress
- Muestra barra de progreso para cada archivo
- Feedback visual de éxito/error
- Integración con AlertService

### Reordenamiento
- Drag & drop entre imágenes para reordenar
- Guardado automático del nuevo orden
- Actualización visual inmediata

### Imagen Principal
- Click en botón de estrella para marcar como principal
- Solo puede haber una imagen principal
- Badge visual "Principal" en la imagen seleccionada

### Eliminación
- Confirmación antes de eliminar
- Elimina de BD y MinIO
- Si se elimina la imagen principal, la siguiente se marca automáticamente como principal

### Preview
- Click en botón de zoom para ver imagen en tamaño completo
- Abre en nueva pestaña

## JavaScript API

```javascript
// El componente expone window.ImageGalleryManager

// Obtener imágenes actuales
const images = ImageGalleryManager.getImages('product-gallery');

// Obtener imagen principal
const primary = ImageGalleryManager.getPrimaryImage('product-gallery');

// Las operaciones se manejan automáticamente:
// - uploadFile()
// - deleteImage()
// - setPrimary()
// - saveOrder()
```

## Migraciones

### Ejecutar migración

```bash
# El archivo ya está creado en:
/database/migrations/2025_01_28_create_product_images_table.php

# Para ejecutar (dependiendo de tu sistema de migraciones):
php migrate.php
# o
php artisan migrate
```

### Rollback

```php
// El método down() ya está definido
Capsule::schema()->dropIfExists('product_images');
```

## Notas Importantes

1. **Bucket de MinIO**: Asegúrate de que el bucket 'products' exista y esté configurado como público

2. **Orden Automático**: Las imágenes se ordenan automáticamente cuando se cargan (último orden + 1)

3. **Primera Imagen**: La primera imagen que se sube automáticamente se marca como `is_primary=true`

4. **Cascade Delete**: Si eliminas un producto, todas sus imágenes se eliminan de la BD (pero no de MinIO automáticamente)

5. **Performance**: El componente usa lazy loading para las imágenes

6. **Theme**: El componente se adapta automáticamente al tema claro/oscuro

## Personalización

### Cambiar tipos de archivo permitidos

```php
$gallery = ImageGalleryComponent::create(
    // ...
    acceptedTypes: ['image/jpeg', 'image/png'] // Solo JPG y PNG
);
```

### Cambiar tamaño máximo

```php
$gallery = ImageGalleryComponent::create(
    // ...
    maxFileSize: 10485760 // 10MB en bytes
);
```

### Cambiar cantidad máxima

```php
$gallery = ImageGalleryComponent::create(
    // ...
    maxFiles: 5 // Máximo 5 imágenes
);
```

## Troubleshooting

### Las imágenes no se suben
1. Verificar que MinIO esté corriendo
2. Verificar configuración en `.env` (STORAGE_* variables)
3. Verificar que el bucket exista
4. Verificar permisos del bucket (debe ser público)

### Error 413 Payload Too Large
Aumentar límites en PHP:
```ini
upload_max_filesize = 10M
post_max_size = 10M
```

### Las imágenes no se eliminan de MinIO
- Verificar que el `key` almacenado en BD sea correcto
- Verificar logs de error en `error_log()`

### El reordenamiento no se guarda
- Verificar que el endpoint `reorder_images` esté disponible
- Verificar console del navegador para errores JavaScript

## Ejemplo Completo

Ver la implementación completa en:
- Componente: `/App/Controllers/Products/Components/ProductFormComponent.php`
- Controller: `/App/Controllers/Products/Controllers/ProductsController.php`
- Modelos: `/app/Models/Product.php` y `/app/Models/ProductImage.php`

---

**Sistema desarrollado con LEGO Framework** 🧱
