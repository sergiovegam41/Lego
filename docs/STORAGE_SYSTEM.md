# 📦 Sistema de Storage - Lego Framework

Sistema completo de almacenamiento de archivos usando MinIO (compatible con S3).

---

## 🚀 **Instalación y Setup**

### **Paso 1: Levantar Servicios**

```bash
# Levantar todos los servicios (incluye MinIO)
docker-compose up -d

# Verificar que MinIO esté corriendo
docker ps | grep minio
```

### **Paso 2: Instalar Dependencias**

```bash
# Instalar AWS SDK y dependencias
docker exec lego-php composer install
```

### **Paso 3: Inicializar Storage**

```bash
# Opción A: Setup completo del framework (incluye storage)
docker exec lego-php php lego init

# Opción B: Solo inicializar storage
docker exec lego-php php lego init:storage
```

**Output esperado:**
```
📦 Inicializando sistema de storage...

→ Verificando conexión con MinIO...
✓ MinIO conectado

→ Configurando bucket 'lego-uploads'...
✓ Bucket 'lego-uploads' creado

→ Aplicando política pública...
✓ Política pública aplicada

→ Creando estructura de carpetas...
  ✓ products/images
  ✓ products/documents
  ✓ catalogs/images
  ✓ users/avatars
  ✓ temp

✓ Sistema de storage inicializado correctamente

📍 Información de conexión:
   Endpoint:  http://localhost:9000
   Bucket:    lego-uploads
   Consola:   http://localhost:9001
```

---

## 🎯 **Acceso a la Consola Web**

**URL:** http://localhost:9001

**Credenciales:**
- Usuario: `minioadmin`
- Password: `minioadmin123`

**Funcionalidades:**
- Ver y gestionar buckets
- Subir/descargar archivos manualmente
- Configurar políticas de acceso
- Ver estadísticas de uso

---

## 🔧 **Comandos CLI**

### **Verificar Estado**

```bash
docker exec lego-php php lego storage:check
```

**Output esperado:**
```
📦 Estado del Sistema de Storage
────────────────────────────────────────────────────────────

🔌 Conexión:
  ✓ MinIO conectado

📍 Endpoints:
  API:      http://localhost:9000
  Consola:  http://localhost:9001

📦 Bucket:
  ✓ 'lego-uploads' (público)

📊 Estadísticas:
  Archivos totales: 15
  Espacio usado:    12.5 MB

📁 Estructura de carpetas:
  📄 products/images/ (10 archivo(s))
  📁 products/documents/ (0 archivo(s))
  📄 catalogs/images/ (3 archivo(s))
  📄 users/avatars/ (2 archivo(s))
  📁 temp/ (0 archivo(s))
```

### **Re-inicializar Storage**

```bash
docker exec lego-php php lego init:storage
```

---

## 💻 **Uso en PHP**

### **API Simple (3 parámetros)**

```php
use Core\Services\Storage\StorageService;

$storage = new StorageService();

// Upload simple
$url = $storage->upload($_FILES['file'], 'producto.jpg', 'products/images/');
// Resultado: http://localhost:9000/lego-uploads/products/images/producto.jpg

// Upload sin nombre personalizado (usa nombre original)
$url = $storage->upload($_FILES['file'], null, 'products/images/');

// Listar archivos
$files = $storage->list('products/images/');
// $files['files'] contiene array de archivos
// $files['count'] contiene cantidad total

// Obtener info de un archivo
$info = $storage->get('products/images/producto.jpg');
// $info['url'], $info['size'], $info['contentType'], etc.

// Eliminar archivo
$storage->delete('products/images/producto.jpg');

// Verificar si existe
if ($storage->exists('products/images/producto.jpg')) {
    // El archivo existe
}

// Copiar archivo
$storage->copy('products/images/producto.jpg', 'backup/producto.jpg');

// Mover archivo
$storage->move('temp/producto.jpg', 'products/images/producto.jpg');

// Estadísticas
$stats = $storage->getStats();
// $stats['totalFiles'], $stats['totalSizeMB'], $stats['fileTypes']
```

### **Manejo de Errores**

```php
use Core\Services\Storage\StorageService;
use Core\Services\Storage\StorageException;

try {
    $storage = new StorageService();
    $url = $storage->upload($_FILES['file'], 'foto.jpg', 'products/images/');

    echo "Archivo subido: {$url}";

} catch (StorageException $e) {
    // Errores específicos de storage
    echo "Error: " . $e->getMessage();
    echo "Código: " . $e->getCode();

    // Códigos de error disponibles:
    // StorageException::CONNECTION_FAILED = 1001
    // StorageException::FILE_NOT_FOUND = 1003
    // StorageException::UPLOAD_FAILED = 1004
    // StorageException::FILE_TOO_LARGE = 1007
    // StorageException::INVALID_EXTENSION = 1008

} catch (\Exception $e) {
    // Otros errores
    echo "Error inesperado: " . $e->getMessage();
}
```

---

## 🌐 **API REST (Testing sin Auth)**

### **1. Upload de Archivo**

**Endpoint:** `POST http://localhost:8080/api/storage/upload`

**Headers:**
```
Content-Type: multipart/form-data
```

**Body (form-data):**
```
file: [seleccionar archivo]
name: producto.jpg (opcional)
path: products/images/ (opcional, default: temp/)
```

**Response (200):**
```json
{
  "success": true,
  "message": "Archivo subido exitosamente",
  "data": {
    "url": "http://localhost:9000/lego-uploads/products/images/producto.jpg",
    "filename": "producto.jpg",
    "path": "products/images/producto.jpg",
    "size": 245678,
    "mimeType": "image/jpeg",
    "uploadedAt": "2025-10-27 14:30:00"
  }
}
```

**cURL Example:**
```bash
curl -X POST http://localhost:8080/api/storage/upload \
  -F "file=@/ruta/a/tu/imagen.jpg" \
  -F "name=producto.jpg" \
  -F "path=products/images/"
```

---

### **2. Listar Archivos**

**Endpoint:** `GET http://localhost:8080/api/storage/list`

**Query Params:**
```
path: products/images/ (opcional)
limit: 50 (opcional, default: 100, max: 1000)
```

**Response (200):**
```json
{
  "success": true,
  "message": "Archivos obtenidos",
  "data": {
    "files": [
      {
        "key": "products/images/producto.jpg",
        "size": 245678,
        "lastModified": "2025-10-27 14:30:00",
        "etag": "abc123",
        "url": "http://localhost:9000/lego-uploads/products/images/producto.jpg"
      }
    ],
    "count": 1,
    "truncated": false,
    "path": "products/images/"
  }
}
```

**cURL Example:**
```bash
curl "http://localhost:8080/api/storage/list?path=products/images/&limit=50"
```

---

### **3. Obtener Info de Archivo**

**Endpoint:** `GET http://localhost:8080/api/storage/get`

**Query Params:**
```
file: products/images/producto.jpg (requerido)
```

**Response (200):**
```json
{
  "success": true,
  "message": "Información del archivo",
  "data": {
    "exists": true,
    "path": "products/images/producto.jpg",
    "url": "http://localhost:9000/lego-uploads/products/images/producto.jpg",
    "size": 245678,
    "sizeMB": 0.23,
    "mimeType": "image/jpeg",
    "lastModified": "2025-10-27 14:30:00"
  }
}
```

**cURL Example:**
```bash
curl "http://localhost:8080/api/storage/get?file=products/images/producto.jpg"
```

---

### **4. Eliminar Archivo**

**Endpoint:** `POST http://localhost:8080/api/storage/delete`

**Headers:**
```
Content-Type: application/json
```

**Body (JSON):**
```json
{
  "file": "products/images/producto.jpg"
}
```

**Response (200):**
```json
{
  "success": true,
  "message": "Archivo eliminado exitosamente"
}
```

**cURL Example:**
```bash
curl -X POST http://localhost:8080/api/storage/delete \
  -H "Content-Type: application/json" \
  -d '{"file":"products/images/producto.jpg"}'
```

---

### **5. Estadísticas**

**Endpoint:** `GET http://localhost:8080/api/storage/stats`

**Response (200):**
```json
{
  "success": true,
  "message": "Estadísticas obtenidas",
  "data": {
    "totalFiles": 342,
    "totalSize": 153427968,
    "totalSizeMB": 146.32,
    "fileTypes": {
      "jpg": 125,
      "png": 89,
      "pdf": 128
    },
    "bucket": "lego-uploads",
    "endpoint": "http://localhost:9000"
  }
}
```

---

## ⚙️ **Configuración**

### **Variables en .env**

```bash
# Configuración de MinIO Storage
MINIO_HOST=minio                    # Host del servidor MinIO
MINIO_PORT=9000                     # Puerto API
MINIO_CONSOLE_PORT=9001             # Puerto consola web
MINIO_ROOT_USER=minioadmin          # Usuario administrador
MINIO_ROOT_PASSWORD=minioadmin123   # Password administrador
MINIO_BROWSER_URL=http://localhost:9001
MINIO_USE_SSL=false                 # true para HTTPS
MINIO_REGION=us-east-1

# Bucket por defecto
MINIO_BUCKET=lego-uploads

# Configuración de uploads
STORAGE_MAX_FILE_SIZE=10485760      # 10MB en bytes
STORAGE_ALLOWED_EXTENSIONS=jpg,jpeg,png,gif,pdf,doc,docx,xls,xlsx,zip
```

### **Cambiar Límites**

Para permitir archivos más grandes:

```bash
# En .env
STORAGE_MAX_FILE_SIZE=52428800  # 50MB

# También actualizar php.ini (si usas PHP-FPM)
upload_max_filesize = 50M
post_max_size = 50M
```

### **Agregar Extensiones Permitidas**

```bash
# En .env
STORAGE_ALLOWED_EXTENSIONS=jpg,jpeg,png,gif,pdf,doc,docx,xls,xlsx,zip,mp4,mov,avi
```

---

## 🔐 **Seguridad**

### **⚠️ Importante: Endpoints sin Autenticación**

Los endpoints actuales **NO tienen autenticación** para facilitar testing en Postman.

**Para producción, agregar middleware:**

```php
// En App/Controllers/Storage/Controllers/StorageController.php

use App\Controllers\Auth\Providers\AuthGroups\Admin\Middlewares\AdminMiddlewares;

public function upload()
{
    // Agregar validación de autenticación
    if (!AdminMiddlewares::isAutenticated()) {
        Response::json(401, new ResponseDTO(false, 'No autorizado'));
        return;
    }

    // ... resto del código
}
```

### **Bucket Público vs Privado**

**Bucket Público (actual):**
- URLs accesibles sin autenticación
- Archivos visibles para cualquiera con la URL
- Ideal para: productos, imágenes públicas

**Para archivos privados:**
```php
// Crear bucket privado
$storage->createBucket('lego-private');
// NO ejecutar setBucketPublic()

// Generar URL temporal (firma que expira)
$url = $storage->getTemporaryUrl('lego-private/factura.pdf', '+1 hour');
```

---

## 🔄 **Migración a AWS S3**

El código es 100% compatible con AWS S3. Solo cambiar variables en `.env`:

```bash
# De MinIO local:
MINIO_HOST=minio
MINIO_PORT=9000
MINIO_USE_SSL=false

# A AWS S3:
MINIO_HOST=s3.amazonaws.com
MINIO_PORT=443
MINIO_USE_SSL=true
MINIO_REGION=us-east-1
MINIO_ROOT_USER=tu-access-key-id
MINIO_ROOT_PASSWORD=tu-secret-access-key
```

**¡El código PHP no cambia!**

---

## 🐛 **Troubleshooting**

### **Error: MinIO no está disponible**

```bash
# Verificar que el contenedor esté corriendo
docker ps | grep minio

# Si no está corriendo, levantarlo
docker-compose up -d minio

# Ver logs
docker logs lego-minio
```

### **Error: Bucket no existe**

```bash
# Ejecutar inicialización
docker exec lego-php php lego init:storage
```

### **Error: Archivo muy grande**

```bash
# Aumentar límite en .env
STORAGE_MAX_FILE_SIZE=52428800  # 50MB
```

### **Error: Extensión no permitida**

```bash
# Agregar extensión en .env
STORAGE_ALLOWED_EXTENSIONS=jpg,jpeg,png,gif,pdf,mp4,mov
```

### **No puedo acceder a archivos subidos**

```bash
# Verificar que el bucket sea público
docker exec lego-php php lego storage:check

# Re-aplicar política pública
docker exec lego-php php lego init:storage
```

---

## 📊 **Estructura de Archivos Creados**

```
/docker-compose.yml                                      [MODIFICADO]
/.env                                                    [MODIFICADO]
/composer.json                                           [MODIFICADO]
/routeMap.json                                           [MODIFICADO]

/Core/Services/Storage/
├── StorageService.php                                   [CREADO]
├── MinioClient.php                                      [CREADO]
├── StorageConfig.php                                    [CREADO]
└── StorageException.php                                 [CREADO]

/Core/Commands/
├── InitStorageCommand.php                               [CREADO]
├── StorageCheckCommand.php                              [CREADO]
└── InitCommand.php                                      [MODIFICADO]

/App/Controllers/Storage/
├── Controllers/
│   └── StorageController.php                            [CREADO]
├── Providers/
│   └── StorageProvider.php                              [CREADO]
└── Rules/
    └── StorageRules.php                                 [CREADO]

/docs/
└── STORAGE_SYSTEM.md                                    [ESTE ARCHIVO]
```

---

## 🎯 **Próximos Pasos**

1. **Agregar Autenticación** - Proteger endpoints en producción
2. **Componentes de Upload** - Crear componente visual de upload con drag & drop
3. **Resize Automático** - Procesar imágenes al subir
4. **Thumbnails** - Generar miniaturas automáticamente
5. **Cleanup Temp** - Script para limpiar archivos temporales antiguos

---

**Sistema implementado por:** Lego Framework Storage System
**Fecha:** 27 de Octubre 2025
**Versión:** 1.0.0
