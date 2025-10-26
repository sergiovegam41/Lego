<?php
/**
 * Generador automático de IDE Helpers para Barrel Exports
 *
 * PROPÓSITO:
 * Genera automáticamente archivos _ide_helper.php basándose en los class_alias
 * definidos en los archivos barrel (Forms.php, Essentials.php, etc.)
 *
 * USO:
 * php scripts/generate-ide-helpers.php
 *
 * O agregar a composer.json:
 * "scripts": {
 *     "post-autoload-dump": "php scripts/generate-ide-helpers.php"
 * }
 */

echo "🔧 Generando IDE Helpers para Barrel Exports...\n\n";

// Configuración de barrels
$barrels = [
    'Forms' => [
        'file' => __DIR__ . '/../components/shared/Forms/Forms.php',
        'namespace' => 'Components\Shared\Forms\Forms',
        'output' => __DIR__ . '/../components/shared/Forms/Forms/_ide_helper.php',
    ],
    'Essentials' => [
        'file' => __DIR__ . '/../components/shared/Essentials/Essentials.php',
        'namespace' => 'Components\Shared\Essentials\Essentials',
        'output' => __DIR__ . '/../components/shared/Essentials/Essentials/_ide_helper.php',
    ],
];

foreach ($barrels as $name => $config) {
    echo "📦 Procesando barrel: {$name}\n";

    if (!file_exists($config['file'])) {
        echo "   ⚠️  Archivo barrel no encontrado: {$config['file']}\n";
        continue;
    }

    // Leer el archivo barrel
    $content = file_get_contents($config['file']);

    // Extraer todos los class_alias
    preg_match_all(
        '/class_alias\(\s*\\\\?([^:]+)::class,\s*[\'"]([^\'"]+)[\'"]\s*\)/i',
        $content,
        $matches,
        PREG_SET_ORDER
    );

    if (empty($matches)) {
        echo "   ⚠️  No se encontraron class_alias en el barrel\n";
        continue;
    }

    // Generar el contenido del helper
    $helperContent = "<?php\n";
    $helperContent .= "/**\n";
    $helperContent .= " * IDE Helper para {$name} Barrel\n";
    $helperContent .= " * \n";
    $helperContent .= " * GENERADO AUTOMÁTICAMENTE - NO EDITAR MANUALMENTE\n";
    $helperContent .= " * Ejecutar: php scripts/generate-ide-helpers.php\n";
    $helperContent .= " * \n";
    $helperContent .= " * Este archivo solo se usa para autocompletado del IDE.\n";
    $helperContent .= " * NO se carga en runtime.\n";
    $helperContent .= " */\n\n";
    $helperContent .= "namespace {$config['namespace']};\n\n";

    foreach ($matches as $match) {
        $originalClass = $match[1];
        $aliasFullName = $match[2];

        // Extraer solo el nombre de la clase del alias
        $aliasClassName = substr($aliasFullName, strrpos($aliasFullName, '\\') + 1);

        $helperContent .= "/**\n";
        $helperContent .= " * @see \\{$originalClass}\n";
        $helperContent .= " */\n";
        $helperContent .= "class {$aliasClassName} extends \\{$originalClass} {}\n\n";
    }

    // Crear directorio si no existe
    $outputDir = dirname($config['output']);
    if (!is_dir($outputDir)) {
        mkdir($outputDir, 0755, true);
    }

    // Escribir el archivo
    file_put_contents($config['output'], $helperContent);

    echo "   ✅ Generado: {$config['output']}\n";
    echo "   📝 " . count($matches) . " clases exportadas\n\n";
}

echo "✨ ¡Completado! Los IDE Helpers han sido generados.\n";
echo "💡 Recarga tu IDE para ver los cambios.\n";
