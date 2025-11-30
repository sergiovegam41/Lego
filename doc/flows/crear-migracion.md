# Crear Migración

## Pasos

### 1. Crear archivo
```
database/migrations/2024_01_15_000001_create_productos_table.php
```

Formato: `YYYY_MM_DD_NNNNNN_descripcion.php`

### 2. Estructura básica
```php
<?php

use Illuminate\Database\Capsule\Manager as DB;
use Illuminate\Database\Schema\Blueprint;

return new class
{
    public function up(): void
    {
        DB::schema()->create('productos', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->text('description')->nullable();
            $table->decimal('price', 10, 2);
            $table->integer('stock')->default(0);
            $table->string('category')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        DB::schema()->dropIfExists('productos');
    }
};
```

### 3. Ejecutar
```bash
php lego migrate
```

## Tipos de Columnas

```php
// Strings
$table->string('name');              // VARCHAR(255)
$table->string('code', 50);          // VARCHAR(50)
$table->text('description');         // TEXT
$table->longText('content');         // LONGTEXT

// Números
$table->integer('stock');
$table->bigInteger('views');
$table->decimal('price', 10, 2);     // DECIMAL(10,2)
$table->float('rating');

// Fechas
$table->date('birth_date');
$table->dateTime('published_at');
$table->timestamp('verified_at');
$table->timestamps();                // created_at, updated_at

// Booleanos
$table->boolean('is_active');

// Otros
$table->json('metadata');
$table->uuid('uuid');
$table->enum('status', ['draft', 'published']);
```

## Modificadores

```php
$table->string('name')->nullable();
$table->integer('stock')->default(0);
$table->string('email')->unique();
$table->foreignId('user_id')->constrained();
$table->foreignId('category_id')->constrained()->onDelete('cascade');
```

## Índices

```php
$table->index('name');
$table->unique('email');
$table->primary(['id', 'code']);
$table->index(['category', 'status']);
```

## Migración de Alteración

```php
return new class
{
    public function up(): void
    {
        DB::schema()->table('productos', function (Blueprint $table) {
            $table->string('sku')->after('name');
            $table->index('sku');
        });
    }

    public function down(): void
    {
        DB::schema()->table('productos', function (Blueprint $table) {
            $table->dropColumn('sku');
        });
    }
};
```

## Migración con Seed

```php
public function up(): void
{
    DB::schema()->create('categorias', function (Blueprint $table) {
        $table->id();
        $table->string('name');
        $table->timestamps();
    });
    
    // Seed inicial
    DB::table('categorias')->insert([
        ['name' => 'Electrónica', 'created_at' => now()],
        ['name' => 'Ropa', 'created_at' => now()],
        ['name' => 'Hogar', 'created_at' => now()],
    ]);
}
```

