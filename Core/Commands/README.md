# 🧱 Lego Framework - CLI Commands System

Sistema escalable de comandos CLI que permite agregar nuevas funcionalidades de forma modular, similar al sistema de componentes de Lego Framework.

## 📁 Arquitectura

```
Core/Commands/
├── CoreCommand.php          # Clase abstracta base
├── CommandRouter.php        # Router que descubre y ejecuta comandos
├── README.md               # Esta documentación
├── InitCommand.php         # php lego init
├── MigrateCommand.php      # php lego migrate
├── MapRoutesCommand.php    # php lego map:routes
├── MakeComponentCommand.php # php lego make:component
└── HelpCommand.php         # php lego help
```

## 🎯 Filosofía

Al igual que los componentes de Lego Framework, cada comando es:
- **Autocontenido**: Una clase que maneja toda su lógica
- **Estandarizado**: Extiende de `CoreCommand`
- **Autodescubrible**: Se registra automáticamente
- **Escalable**: Agregar nuevos comandos es trivial

## ⚡ Comandos Disponibles

### Core Commands
- `php lego help` - Mostrar ayuda general o de comandos específicos
- `php lego init` - Inicializar framework (migrate + map:routes)
- `php lego migrate` - Ejecutar migraciones de base de datos
- `php lego map:routes` - Mapear rutas de controladores

### Development Commands
- `php lego make:component <name>` - Crear nuevo componente Lego

### Utility Commands
- `php lego --version` - Mostrar versión del CLI
- `php lego help <command>` - Ayuda específica de comando

## 🔧 Crear Nuevos Comandos

### 1. Crear la Clase

Crea un nuevo archivo en `Core/Commands/`:

```php
<?php

namespace Core\Commands;

class MyCustomCommand extends CoreCommand
{
    protected string $name = 'my:command';
    protected string $description = 'Description of what this command does';
    protected string $signature = 'my:command {arg1} [--option=default]';

    public function execute(): bool
    {
        $arg1 = $this->argument(1); // Primer argumento
        $option = $this->option('option', 'default'); // Opción con default

        $this->info("Executing my custom command with {$arg1}");

        try {
            // Tu lógica aquí
            $this->success("Command completed successfully!");
            return true;
        } catch (\Exception $e) {
            $this->error("Command failed: " . $e->getMessage());
            return false;
        }
    }
}
```

### 2. Uso Automático

¡Eso es todo! El comando será automáticamente:
- ✅ Descubierto por `CommandRouter`
- ✅ Listado en `php lego help`
- ✅ Ejecutable como `php lego my:command`

## 🎨 Métodos Disponibles en CoreCommand

### Salida con Colores
```php
$this->success("✅ Operación exitosa");
$this->error("❌ Error ocurrido");
$this->warning("⚠️ Advertencia");
$this->info("ℹ️ Información");
$this->line("Texto normal");
```

### Argumentos y Opciones
```php
$arg = $this->argument(1, 'default');           // Argumento posicional
$option = $this->option('force', false);        // Opción --force
$flag = $this->option('verbose', false);        // Flag --verbose
```

### Interacción
```php
$confirmed = $this->confirm('¿Continuar?');      // Confirmación y/n
$this->progressBar($current, $total, 'Loading'); // Barra de progreso
```

## 📋 Ejemplos de Uso

### Comando Básico
```bash
php lego migrate
php lego map:routes
php lego init
```

### Comando con Argumentos
```bash
php lego make:component UserCard
php lego make:component ProductList --path=Shop --type=list
```

### Ayuda
```bash
php lego help                    # Ayuda general
php lego help make:component     # Ayuda específica
php lego --version               # Versión
```

## 🔄 Ventajas del Sistema

### ✅ **Escalabilidad**
- Agregar comandos no requiere modificar el archivo `lego` principal
- Autodescubrimiento automático de nuevos comandos
- Sin configuración adicional requerida

### ✅ **Consistencia**
- Interfaz estándar mediante `CoreCommand`
- Colores y formato unificado
- Manejo de errores consistente

### ✅ **Mantenibilidad**
- Cada comando en su propio archivo
- Lógica encapsulada y testeable
- Fácil debug y modificación

### ✅ **Developer Experience**
- Help integrado automático
- Sintaxis familiar (similar a Artisan/Symfony Console)
- Feedback visual con colores y emojis

## 🎯 Futuras Mejoras

- [ ] **Validación de argumentos** automática basada en `signature`
- [ ] **Autocompletado** de comandos en shell
- [ ] **Comandos interactivos** con prompts avanzados
- [ ] **Testing helpers** para comandos
- [ ] **Logging** integrado de ejecución de comandos
- [ ] **Hooks** antes/después de ejecución

---

> **"Cada comando es una pieza LEGO que encaja perfectamente en el sistema"** 🧱
>
> Con este sistema, expandir la funcionalidad CLI es tan fácil como crear un nuevo componente.