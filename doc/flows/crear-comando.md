# Crear Comando

Comandos CLI para tareas de mantenimiento.

## Pasos

### 1. Crear archivo
```php
// Core/Commands/MiComando.php

<?php
namespace Core\Commands;

class MiComando
{
    public function __construct()
    {
        $this->execute();
    }
    
    private function execute(): void
    {
        $this->info("Iniciando proceso...");
        
        // Tu lógica aquí
        
        $this->success("Proceso completado");
    }
    
    private function info(string $msg): void
    {
        echo "\033[34m[INFO]\033[0m $msg\n";
    }
    
    private function success(string $msg): void
    {
        echo "\033[32m[OK]\033[0m $msg\n";
    }
    
    private function error(string $msg): void
    {
        echo "\033[31m[ERROR]\033[0m $msg\n";
    }
}
```

### 2. Registrar en lego CLI
```php
// lego (archivo en root)

// Agregar en el switch de comandos:
case 'mi-comando':
    require_once 'Core/Commands/MiComando.php';
    new \Core\Commands\MiComando();
    break;
```

### 3. Ejecutar
```bash
php lego mi-comando
```

## Ejemplo: Comando con Argumentos

```php
class ImportarDatos
{
    private array $args;
    
    public function __construct(array $args = [])
    {
        $this->args = $args;
        $this->execute();
    }
    
    private function execute(): void
    {
        $file = $this->args[0] ?? null;
        
        if (!$file) {
            $this->error("Uso: php lego importar <archivo>");
            return;
        }
        
        if (!file_exists($file)) {
            $this->error("Archivo no encontrado: $file");
            return;
        }
        
        $this->info("Importando desde $file...");
        // Lógica de importación
        $this->success("Importación completada");
    }
}
```

Registro:
```php
case 'importar':
    new \Core\Commands\ImportarDatos(array_slice($argv, 2));
    break;
```

Uso:
```bash
php lego importar datos.csv
```

## Ejemplo: Comando Interactivo

```php
class SetupWizard
{
    public function __construct()
    {
        $this->run();
    }
    
    private function run(): void
    {
        $this->info("=== Setup Wizard ===\n");
        
        $dbHost = $this->ask("Host de base de datos", "localhost");
        $dbName = $this->ask("Nombre de base de datos");
        $dbUser = $this->ask("Usuario", "root");
        $dbPass = $this->askSecret("Contraseña");
        
        // Guardar configuración
        $this->success("Configuración guardada");
    }
    
    private function ask(string $question, string $default = ''): string
    {
        $defaultText = $default ? " [$default]" : '';
        echo "$question$defaultText: ";
        $input = trim(fgets(STDIN));
        return $input ?: $default;
    }
    
    private function askSecret(string $question): string
    {
        echo "$question: ";
        system('stty -echo');
        $input = trim(fgets(STDIN));
        system('stty echo');
        echo "\n";
        return $input;
    }
}
```

## Comandos Existentes

```bash
php lego migrate          # Ejecutar migraciones
php lego config:reset     # Resetear configuración y menú
php lego storage:init     # Inicializar storage
php lego cache:clear      # Limpiar caché
```

