#!/usr/bin/env php
<?php
/**
 * LEGO Framework - CRUD Generator
 *
 * Genera automáticamente un CRUD completo siguiendo los patrones del framework:
 * - Model con atributos ApiGetResource y ApiCrudResource
 * - Migration con tabla y campos
 * - Component principal (lista con tabla)
 * - Component Create (formulario)
 * - Component Edit (formulario)
 * - CSS con variables reactivas al tema
 * - JavaScript con patrón correcto para módulos dinámicos
 * - Entrada en el menú
 * - Composer autoload
 *
 * Uso:
 *   php scripts/generate-crud.php <ModelName> <field1:type> <field2:type> ...
 *
 * Ejemplo:
 *   php scripts/generate-crud.php Testimonial author:string message:text
 */

// Colores para terminal
define('COLOR_GREEN', "\033[32m");
define('COLOR_YELLOW', "\033[33m");
define('COLOR_RED', "\033[31m");
define('COLOR_BLUE', "\033[34m");
define('COLOR_RESET', "\033[0m");

function printSuccess($message) {
    echo COLOR_GREEN . "✅ " . $message . COLOR_RESET . "\n";
}

function printInfo($message) {
    echo COLOR_BLUE . "ℹ️  " . $message . COLOR_RESET . "\n";
}

function printWarning($message) {
    echo COLOR_YELLOW . "⚠️  " . $message . COLOR_RESET . "\n";
}

function printError($message) {
    echo COLOR_RED . "❌ " . $message . COLOR_RESET . "\n";
}

// Verificar argumentos
if ($argc < 3) {
    printError("Uso: php scripts/generate-crud.php <ModelName> <field1:type> <field2:type> ...");
    printError("Ejemplo: php scripts/generate-crud.php Testimonial author:string message:text");
    exit(1);
}

$modelName = $argv[1];
$fields = array_slice($argv, 2);

// Validar nombre del modelo
if (!preg_match('/^[A-Z][a-zA-Z0-9]*$/', $modelName)) {
    printError("El nombre del modelo debe empezar con mayúscula y contener solo letras y números");
    exit(1);
}

printInfo("🚀 Generando CRUD para: $modelName");
printInfo("📝 Campos: " . implode(', ', $fields));
echo "\n";

// Parsear campos
$parsedFields = [];
foreach ($fields as $field) {
    $parts = explode(':', $field);
    if (count($parts) !== 2) {
        printError("Campo inválido: $field. Use formato campo:tipo");
        exit(1);
    }

    [$fieldName, $fieldType] = $parts;

    // Validar tipo
    $validTypes = ['string', 'text', 'integer', 'decimal', 'boolean', 'date', 'datetime'];
    if (!in_array($fieldType, $validTypes)) {
        printError("Tipo inválido: $fieldType. Tipos válidos: " . implode(', ', $validTypes));
        exit(1);
    }

    $parsedFields[] = [
        'name' => $fieldName,
        'type' => $fieldType,
    ];
}

// Generar nombres y rutas
$modelNamePlural = pluralize($modelName);
$modelNameKebab = toKebabCase($modelNamePlural);
$modelNameLower = strtolower($modelName);
$modelNameLowerPlural = strtolower($modelNamePlural);

$timestamp = date('Y_m_d_His');
$migrationName = $timestamp . '_create_' . $modelNameLower . 's_table.php';

printInfo("📁 Estructura:");
printInfo("   - Model: App/Models/{$modelName}.php");
printInfo("   - Migration: database/migrations/{$migrationName}");
printInfo("   - Component: components/App/{$modelNamePlural}/{$modelNamePlural}Component.php");
printInfo("   - Create: components/App/{$modelNamePlural}/childs/{$modelName}Create/");
printInfo("   - Edit: components/App/{$modelNamePlural}/childs/{$modelName}Edit/");
echo "\n";

// Confirmar
echo "¿Deseas continuar? (y/n): ";
$handle = fopen("php://stdin", "r");
$line = fgets($handle);
fclose($handle);

if (trim($line) !== 'y' && trim($line) !== 'Y') {
    printWarning("Operación cancelada");
    exit(0);
}

echo "\n";
printInfo("🔨 Generando archivos...");
echo "\n";

// TODO: Implementar generación de archivos
// 1. Modelo
// 2. Migration
// 3. Component principal
// 4. Component Create
// 5. Component Edit
// 6. CSS
// 7. JavaScript
// 8. Actualizar composer.json
// 9. Actualizar menú

printWarning("⚠️  Generador en desarrollo - Archivos base creados manualmente por ahora");
printInfo("📚 Revisa los ejemplos en: Flowers, Categories, Testimonials");

// Helper functions
function pluralize($word) {
    $irregulars = [
        'person' => 'people',
        'child' => 'children',
        'man' => 'men',
        'woman' => 'women',
    ];

    $lower = strtolower($word);
    if (isset($irregulars[$lower])) {
        return ucfirst($irregulars[$lower]);
    }

    if (substr($lower, -1) === 'y') {
        return substr($word, 0, -1) . 'ies';
    }

    if (in_array(substr($lower, -1), ['s', 'x', 'z']) ||
        in_array(substr($lower, -2), ['ch', 'sh'])) {
        return $word . 'es';
    }

    return $word . 's';
}

function toKebabCase($string) {
    $result = preg_replace('/([a-z])([A-Z])/', '$1-$2', $string);
    return strtolower($result ?? $string);
}
